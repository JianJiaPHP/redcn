<?php
# 支付宝支付
namespace App\Utils;

use App\Models\chat\Goods;
use App\Models\Pay\PayOrder;
use App\Services\Api\OrderService;
use Carbon\Carbon;
use Exception;
use Log;
use Psr\Http\Message\ResponseInterface;
use Yansongda\Pay\Exception\ContainerException;
use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Pay;

class WeChatPay
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
        $alipay = Pay::wechat(config('pay'));
        try {
            $result = Pay::wechat()->callback();
            // 校验 app_id是否为该商户本身
            if (env('ZHIFUBAO_APPID') != $result['app_id']) {
                Log::debug('异步回调->商户id不符！' . Carbon::now());
                return false;
            }
            // 判断业务板块 根据orderID 查询order表 根据meal_type 判断执行指向业务代码
            if ($result['trade_status'] == 'TRADE_SUCCESS' || $result['trade_status'] == 'TRADE_FINISHED') {
                $orderInfo = PayOrder::query()->where([
                    'order_no' => $result['out_trade_no'],
                    ['pay_status', '!=', '3'],
                    'amount'   => $result['total_amount']
                ])->select(['user_id', 'goods_id'])->first();
                if (!$orderInfo) {
                    Log::debug('异步回调订单错误！' . Carbon::now());
                    return false;
                }
                $orderInfo = $orderInfo->toArray();
                # 根据商品id 查询是什么商品
                $meal_type = Goods::query()->where('id', $orderInfo['goods_id'])->value('type');
                PayOrder::query()->where('order_no', $result['out_trade_no'])->update(['pay_status' => 3]);  //修正订单状态
                if ($meal_type) {
                    $orderServer = new OrderService();
                    $res['code'] = 500;
                    switch ($meal_type) {
                        case 1:
                            // 处理gpt购买
                            $res = $orderServer->gptPay((int)$orderInfo['user_id'], $result['out_trade_no']);
                            break;
                        case 3:
                            // 处理提现转账
                            $res = $orderServer->membershipPay((int)$orderInfo['user_id'], $result['out_trade_no']);
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
     * DateTime: 2023/5/11
     * @param $data
     * 微信wap支付
     * @throws Exception
     */
    public function wap_pay(array $data, $ip): string
    {
        try {
            $order = [
                'out_trade_no' => $data['out_trade_no'],
                'description'  => $data['subject'],
                'amount'       => [
                    'total' => $data['total_amount'],
                ],
                'scene_info'   => [
                    'payer_client_ip' => $ip,
                    'h5_info'         => [
                        'type' => 'Wap',
                    ]
                ],
            ];
            return Pay::wechat(config('pay'))->wap($order);
        } catch (Exception $e) {
            Log::debug($e->getMessage() . '-------' . Carbon::now());
            throw new Exception($e->getMessage());
        }
    }

}
