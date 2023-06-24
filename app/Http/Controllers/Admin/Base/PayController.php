<?php


namespace App\Http\Controllers\Admin\Base;


use App\Http\Controllers\Controller;
use App\Http\Requests\PayRequests;
use App\Models\Pay\PayOrder;
use App\Models\Store\Store;
use App\Models\Store\StoreCouponCode;
use App\Models\Store\StoreOrder;
use App\Utils\Alipay;
use App\Utils\Result;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Log;

class PayController extends Controller
{

    protected $payUtils;

    public function __construct(Alipay $pay)
    {
        $this->payUtils = $pay;
    }

    /**
     * User: Yan
     * DateTime: 2023/3/7
     * @param PayRequests $request
     * @return JsonResponse
     * 支付宝支付-生成付款二维码
     */
    public function pay(PayRequests $request): JsonResponse
    {
        try {
            $admin_id = auth('api')->id();
            if (!$admin_id) {
                return Result::fail("用户ID获取失败，请重试");
            }
            $admin_info = auth('api')->user();
            if ($admin_info['store_id'] > 0) {
                $request['store_id'] = $admin_info['store_id'];
            }


            # 查询商户是否存在
            $store = Store::query()->where(['id' => $request['store_id']])->exists();
            if (!$store) {
                return Result::fail("商户不存在");
            }

            $dataParams = [
                'out_trade_no' => 'sy' . env('APP_LEVEL') . date('Ymd') . str_pad(mt_rand(1, 9999999), 5, '0', STR_PAD_LEFT),
                'total_amount' => $request['total_amount'],
                'subject'      => $request['subject'],
            ];


            //生成支付宝二维码
            $data = $this->payUtils->scan_pay($dataParams);

            //添加订单
            if ($data->code == 10000) {
                $createPay = [
                    'order_no'     => $dataParams['out_trade_no'],//统一下单订单号
                    'total_amount' => $request['total_amount'],//支付金额
                    'meal_id'      => $request['meal_id'],//套餐id
                    'meal_type'    => $request['meal_type'],//支付业务类型
                    'pay_type'     => 1,//支付类型 支付宝：1
                    'store_id'     => $request['store_id'],
                ];
                $createPay['json'] = json_encode($createPay);
                PayOrder::query()->create($createPay);
            }
            return Result::success($data);
        } catch (Exception $e) {

            return Result::fail($e->getMessage());
        }

    }

    /**
     * User: Yan
     * DateTime: 2023/3/7
     * @param Alipay $pay
     * @return JsonResponse
     * 购买套餐回调
     */
    public function notify(Alipay $pay): JsonResponse
    {
        $data = $pay->notify();
        return Result::success($data);
    }

    /**
     * User: Yan
     * DateTime: 2023/3/7
     * 查询支付状态
     */
    public function queryResults(): JsonResponse
    {
        $params = request()->all();

        if (empty($params['order_no'])) {
            return Result::fail("order_no fail");
        }
        $data = $this->payUtils->queryResults($params);

        return Result::success($data);
    }


    /**
     * User: Yan
     * DateTime: 2023/3/13
     * @return void
     * 抖音退款订单回调
     * @throws Exception
     */
    public function douYinNotify()
    {
        $params = request()->all();
        Log::info('抖音退款订单回调', $params);
        $token = "adf54a6df48we9f49asdfsa9f4";
        $sortedString = [$token, $params['timestamp'], $params['nonce'], $params['msg']];
        sort($sortedString, SORT_STRING);
        $concat = implode("", $sortedString);
        $arrayByte = utf8_encode($concat);
        $digestByte = sha1($arrayByte, true);
        $signBuilder = "";
        foreach (str_split($digestByte) as $b) {
            $signBuilder .= sprintf("%02x", ord($b));
        }
        $Signature = $params['msg_signature'];
        if ($Signature != $signBuilder) {
            Log::info('签名验证失败', $params);
            throw new Exception("签名验证失败");
        }
        # 回调成功 修改订单状态
        $data = json_decode($params['msg'], true);
        # 成功回调
        if ($data['status'] == 'SUCCESS') {
            Log::info('抖音退款订单回调--ok', $params);
            StoreOrder::query()->where('order_no', $data['cp_extra'])->update(['refund_status' => 2,'status' => -2, 'refund_reason_time' => Carbon::now()]);
            #验证是否是核销订单数据【如果是核销订单数据那么下面那张表中就会有数据】
            if (StoreCouponCode::query()->where(['order_no' => $data['cp_extra'], 'status' => 4])->value('id')) {
                #订单核销表
                StoreCouponCode::query()->where(['order_no' => $data['cp_extra'], 'status' => 4])->update([
                    'status' => 2
                ]);
            }
        } else {
            Log::error('抖音退款订单回调--fail', $params);
            StoreOrder::query()->where('order_no', $data['cp_extra'])->update(['refund_status' => 7]);
            #验证是否是核销订单数据【如果是核销订单数据那么下面那张表中就会有数据】
            if (StoreCouponCode::query()->where(['order_no' => $data['cp_extra'], 'status' => 4])->value('id')) {
                #如果退款失败就改回状态把，最少能让用户继续使用
                StoreOrder::query()->where('order_no', $data['cp_extra'])->update(['refund_status' => 0,'status' => 2]);
                #订单核销表
                StoreCouponCode::query()->where(['order_no' => $data['cp_extra'], 'status' => 4])->update([
                    'status' => 0
                ]);
            }
        }
    }


    /**
     * User: Yan
     * DateTime: 2023/3/13
     * @return JsonResponse 发起抖音退款
     * 发起抖音退款
     */
    public function douYinSendRefund(): JsonResponse
    {
        $params = request()->all();
        if (empty($params['order_id'])) {
            return Result::fail("缺少参数order_id订单id");
        }
        try {
            $data = $this->payUtils->douYinSendRefund($params['order_id'], $params['msg'] ?? '');
            return Result::success($data);
        } catch (Exception $e) {
            return Result::fail($e->getMessage());
        }
    }

}
