<?php

namespace App\Services\Api;

use App\Exceptions\ApiException;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;

class Pay2Service
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
            $url = "https://jinniuzf.xyz/Pay";
            switch ($type) {
                case 1:
                    $productId = 202;
                    break;
                case 2:
                    $productId = 111;
                    break;
                #其他
                default:
                    throw new Exception('支付类型错误');
            }
            $sendData = [
                'pay_memberid'    => 230688854,//商户ID
                'pay_bankcode'    => $productId,//通道
                'pay_orderid'     => $order_no,//商户订单号
                'pay_amount'      => $amount,//支付金额
//                'notifyUrl'    => env('APP_URL') . '/api/notify',//支付结果后台回调URL
                'pay_notifyurl'   => '/api/notify',//支付结果后台回调URL
                'pay_callbackurl' => '/api/notify',//支付结果后台回调URL
            ];
            $key = 'QHtiE4qa3IliuD8uX2ao7oHhxoQG25jT';
            $sendData['pay_md5sign'] = $this->generateSign($sendData, $key);
            $httpArr = [
                'multipart' => [],
            ];
            foreach ($sendData as $key => $value) {
                $httpArr['multipart'][] = [
                    'name'     => $key,
                    'contents' => $value,
                ];
            }
            $client = new Client([
                'timeout'              => 5.0,
                RequestOptions::VERIFY => public_path('cacert-2023-01-10.pem')
            ]);
            $response = $client->request('POST', $url, $httpArr);
            $result = json_decode($response->getBody()->getContents(), true);
            if ($result['status'] !== '200') {
                throw new ApiException('充值失败，请重试，或者更换渠道');
            }
            //array:3 [2
            //  "status" => "200"
            //  "type" => "url"
            //  "data" => "http://47.57.141.100:54321/api/baidu/pay?order_sn=202306302252225410143613"
            //]
            //1
            //"status": "200",
            //	"type": "url",
            //	"data": "http://120.78.164.22/MobileDeposit/Wait.html?money=100.00&PayOrderNumber=202224420230630225342541019NnZ&PayType=wechatH5&BussinessType=1&RequestCount=58"
            return ['payUrl' => $result['data'], 'tradeNo' => $order_no,'recharge_id'=>3];
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

    /**
     * @throws Exception|GuzzleException
     */
    public function orderQuery($order_no): bool
    {
        try {
            $url = "https://jinniuzf.xyz/Query";
            $sendData = [
                'pay_memberid'    => 230688854,//商户ID
                'pay_orderid'    => $order_no,//通道
            ];
            $key = 'QHtiE4qa3IliuD8uX2ao7oHhxoQG25jT';
            $sendData['pay_md5sign'] = $this->generateSign($sendData, $key);
            $httpArr = [
                'multipart' => [],
            ];
            foreach ($sendData as $key => $value) {
                $httpArr['multipart'][] = [
                    'name'     => $key,
                    'contents' => $value,
                ];
            }
            $client = new Client([
                'timeout'              => 5.0,
                RequestOptions::VERIFY => public_path('cacert-2023-01-10.pem')
            ]);
            $response = $client->request('POST', $url, $httpArr);
            $result = json_decode($response->getBody()->getContents(), true);
            if (empty($result)) {
                throw new ApiException('充值失败，请重试，或者更换渠道');
            }
            if ($result['trade_state'] == 'SUCCESS'){
                return true;
            }
            return false;
        }catch (Exception $e){
            throw new Exception($e->getMessage());
        }

    }

}
