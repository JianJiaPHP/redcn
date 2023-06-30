<?php

namespace App\Services\Api;

use App\Exceptions\ApiException;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;

class Pay1Service
{
    /**
     * @throws GuzzleException
     * @throws Exception
     */
    # 统一下单 POST
    # 交易金额：默认为人民币交易，单位为分，参数值不能带小数。
    public function orderCreate($amount, $order_no, $type)
    {
        try {
            $url = "https://anyipayih52ioq8d.zzbbm.xyz/api/pay/unifiedorder";
            switch ($type) {
                case 2:
                    $productId = '101';
                    break;
                #其他
                default:
                    throw new Exception('支付类型错误,请更换类型通道');
            }
            $sendData = [
                'mchId'      => 'M1687593174',//商户ID
                'wayCode'    => $productId,//产品ID
                'subject'    => '充值',
                'outTradeNo' => $order_no,//商户订单号
                'amount'     => 10000,//支付金额
                'extParam'   => $order_no,//支付金额
                'notifyUrl'  => env('APP_URL') . '/api/notify',//支付结果后台回调URL
                'returnUrl'  => env('APP_URL') . '/api/notify',//支付结果后台回调URL
                'reqTime'    => time()
            ];
            $key = 'f86ebc7cd62c49a296186f94a634c906';
            $sendData['sign'] = $this->generateSign($sendData, $key);
            $paramsArr = [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'body'    => json_encode($sendData)
            ];
            $client = new Client([
                'timeout'              => 5.0,
                RequestOptions::VERIFY => public_path('cacert-2023-01-10.pem')
            ]);
            $response = $client->request('POST', $url, $paramsArr);
            $result = json_decode($response->getBody()->getContents(), true);
            if ($result['code'] !== '0') {
                throw new ApiException('充值失败，请重试，或者更换渠道');
            }
            return ['payUrl'=>$result['data']['payUrl'],'tradeNo'=>$result['data']['tradeNo'],'recharge_id'=>2];
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    function generateSign($data, $keys): string
    {
        ksort($data);
        $stringA = '';
        foreach ($data as $key => $value) {
            if ($value !== '') {
                $stringA .= $key . '=' . $value . '&';
            }
        }
        $stringA = rtrim($stringA, '&');
        $stringSignTemp = $stringA . '&key=' . $keys;

        return strtoupper(md5($stringSignTemp));
    }


    # 查询订单 POST

    public function orderQuery()
    {
        $url = "https://anyipayih52ioq8d.zzbbm.xyz/api/pay/query";
    }


}
