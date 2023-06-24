<?php
# 支付宝支付
namespace App\Utils;

use App\Models\chat\Goods;
use App\Models\Pay\PayOrder;
use App\Services\Admin\Pay\PayNotifyService;
use App\Services\Api\OrderService;
use Carbon\Carbon;
use Exception;
use Log;
use Psr\Http\Message\ResponseInterface;
use Yansongda\Pay\Exception\ContainerException;
use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Pay;
use Yansongda\Supports\Collection;

class Alipay
{

    /**
     * User: Yan
     * DateTime: 2023/3/7
     * @return ResponseInterface
     * 同步回调
     * @throws ContainerException
     * @throws InvalidParamsException
     */
    public function return(): ResponseInterface
    {
        $alipay = Pay::alipay(config('pay'));
        $data = $alipay->callback(); // 是的，验签就这么简单！
        Log::debug('同步回调了！' . Carbon::now(), $data->all());
        return Pay::alipay(config('pay'))->success();// laravel 框架中请直接 `return $alipay->success()`
    }

    /**
     * User: Yan
     * DateTime: 2023/3/7
     * 异步回调
     */
    public function notify()
    {
        $alipay = Pay::alipay(config('pay'));
        $payNotify = new PayNotifyService();
        try {
            $result = Pay::alipay()->callback();
            Log::info('支付宝回调', [$result]);
            // 校验 app_id是否为该商户本身
            if (env('ZHIFUBAO_APPID') != $result['app_id']) {
                Log::debug('异步回调->商户id不符！' . Carbon::now());
                return false;
            }
            // 判断业务板块 根据orderID 查询order表 根据meal_type 判断执行指向业务代码
            if ($result['trade_status'] == 'TRADE_SUCCESS' || $result['trade_status'] == 'TRADE_FINISHED') {
                $orderInfo = PayOrder::query()->where([
                    'order_no'     => $result['out_trade_no'],
                    ['pay_status', '!=', '3'],
//                    'amount' => $result['total_amount']
                ])->select(['user_id', 'goods_id'])->first();
                if (!$orderInfo) {
                    Log::debug('异步回调订单错误！' . Carbon::now());
                    return false;
                }
                $orderInfo = $orderInfo->toArray();
                # 根据商品id 查询是什么商品
                $meal_type = Goods::query()->where('id', $orderInfo['goods_id'])->value('type');
                PayOrder::query()->where('order_no', $result['out_trade_no'])->update(['pay_status' => 3,'pay_time'=>Carbon::now()]);  //修正订单状态
                if ($meal_type) {
                    $orderServer = new OrderService();
                    $res['code'] = 500;
                    switch ($meal_type) {
                        case 1:
                            // 处理gpt购买
                            $res = $orderServer->gptPay((int)$orderInfo['user_id'],$result['out_trade_no']);
                            break;
                        case 2:
                            $res = $orderServer->gptPay((int)$orderInfo['user_id'],$result['out_trade_no']);
                            break;
                        case 3:
                            // 处理社群购买
                            $res = $orderServer->membershipPay((int)$orderInfo['user_id'],$result['out_trade_no']);
                            break;
                    }
                    if ($res['code'] != 200) {
                        Log::debug('异步回调订单错误！' . Carbon::now() . json_encode($res));
                    }
                } else {
                    Log::debug('异步回调订单错误！或已经处理过了' . Carbon::now());
                }
            }
        } catch (Exception $e) {
            Log::debug($e->getMessage() . '-------' . Carbon::now());
        }
        return $alipay->success();
    }

    /**
     * User: Yan
     * DateTime: 2023/3/7
     * @return Collection
     * 支付宝生成付款二维码
     * @throws Exception
     */
    public function scan_pay($data): Collection
    {

        try {
            $order = [
                'out_trade_no' => $data['out_trade_no'],
                'total_amount' => $data['total_amount'],
                'subject'      => $data['subject'],
            ];
            return Pay::alipay(config('pay'))->scan($order);
        } catch (ContainerException $e) {
            Log::debug($e->getMessage() . '-------' . Carbon::now());
            throw new Exception($e->getMessage());
        }
    }

    /**
     * User: Yan
     * DateTime: 2023/5/11
     * @param $data
     * 支付宝wap支付
     * @throws Exception
     */
    public function wap_pay($data): string
    {
        try {
            $order = [
                'out_trade_no' => $data['out_trade_no'],
                'total_amount' => $data['total_amount'],
                'subject'      => $data['subject'],
                '_method'      => 'get',
            ];
            return Pay::alipay(config('pay'))->wap($order)->getHeaderLine('Location');
        } catch (Exception $e) {
            Log::debug($e->getMessage() . '-------' . Carbon::now());
            throw new Exception($e->getMessage());
        }
    }

    /**
     *  /**
     * User:Deng
     * DateTime: 2023/3/7
     * 支付宝转账
     * @param $data
     * @return Collection
     */
    public function alipay_transfer($data): Collection
    {
        $order = [
            'out_biz_no'   => $data['out_trade_no'],
            'trans_amount' => $data['trans_amount'],
            'product_code' => 'TRANS_ACCOUNT_NO_PWD',
            'biz_scene'    => 'DIRECT_TRANSFER',
            'order_title'  => $data['order_title'],
            'payee_info'   => [
                'identity'      => $data['ali_pay_account'],
                'identity_type' => 'ALIPAY_LOGON_ID',
                'name'          => $data['real_name']
            ],
            'remark'       => '转账'
        ];
        return Pay::alipay(config('pay'))->transfer($order);
    }

    /**
     * User: Yan
     * DateTime: 2023/3/7
     * @param $params
     * 轮询查询 支付状态
     */
    public function queryResults($params)
    {
        return PayOrder::query()->where($params)->value('pay_status');//'支付状态 1：待支付 2：已支付 3：已回调 4：已取消/支付失败'
    }

    /**
     * User: Yan
     * DateTime: 2023/3/13
     * @param $order_id
     * @param string $msg
     * @return string 发起抖音退款
     * 发起抖音退款
     * @throws Exception
     */
    public function douYinSendRefund($order_id, string $msg): string
    {
        try {
            # 调用抖音发起退款
            $douYinApi = new DouYinSendApi();
            $orderNo = StoreOrder::query()->where('id', $order_id)->value('order_no');
            $refund_no = 'refund_on_' . $orderNo . '_' . Carbon::now()->timestamp;
            # 获取订单数据
            $orderInfo = StoreOrder::query()->where('id', $order_id)->select([
                'id', 'order_no', 'total_price', 'source_id'
            ])->first();
            if (empty($orderInfo)) {
                throw new Exception('订单不存在');
            }

            $res = $douYinApi->post_send_refund($refund_no, $orderInfo);
            # 订单状态变化
            StoreOrder::query()->where('id', $order_id)->update([
                'refund_status'      => 5,
                'refund_no'          => $refund_no,
                'refund_remark'      => $msg ?? "用户取消订单",
                'refund_reason'      => $msg ?? "用户取消订单",
                'refund_reason_time' => Carbon::now(),
                'refund_sign'        => $res['sign'],
            ]);
            return $res['msg'];
        } catch (Exception $e) {
            Log::debug($e->getMessage() . '-------' . Carbon::now());
            throw new Exception($e->getMessage());
        }
    }

    /**
     * User: AnJiLan
     * DateTime: 2023/4/13
     * @param $order_id
     * @param string $msg
     * @return string 发起抖音退款[商品核销卷，折扣卷]
     * 发起抖音退款
     * @throws Exception
     */
    public function douYinSendRefundProduct($order_id, string $msg): string
    {
        try {
            # 调用抖音发起退款
            $douYinApi = new DouYinSendApi();
            $orderNo = StoreOrder::query()->where('id', $order_id)->value('order_no');
            $refund_no = 'refund_on_' . $orderNo . '_' . Carbon::now()->timestamp;
            # 获取订单数据
            $orderInfo = StoreOrder::query()->where('id', $order_id)->select([
                'id', 'order_no', 'total_price', 'source_id'
            ])->first();
            if (empty($orderInfo)) {
                throw new Exception('订单不存在');
            }
            $totalPrice = StoreCouponCode::query()->where(['order_no' => $orderNo, 'status' => 0])->sum('price');
            $orderInfo['total_price'] = $totalPrice;
            $res = $douYinApi->post_send_refund($refund_no, $orderInfo);
            # 订单状态变化
            StoreOrder::query()->where('id', $order_id)->update([
                'refund_status'     => 5,
                'refund_no'         => $refund_no,
                'refund_remark'     => $msg ?? "用户取消订单",
                'refund_reason'     => $msg ?? "用户取消订单",
//                'refund_reason_time' => Carbon::now(),
                'refund_sign'       => $res['sign'],
                'refund_request_at' => Carbon::now(),//退款申请时间
                'status'            => -2
            ]);
            StoreCouponCode::query()->where(['order_no' => $orderNo, 'status' => 0])->update([
                'status' => 4,
            ]);

            return $res['msg'];
        } catch (Exception $e) {
            Log::debug($e->getMessage() . '-------' . Carbon::now());
            throw new Exception($e->getMessage());
        }
    }
}
