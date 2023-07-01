<?php

namespace App\Services\Api;

use App\Exceptions\ApiException;
use App\Models\RechargeLog;
use DB;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;

class PayService
{
    /**
     * @throws GuzzleException
     * @throws Exception
     */
    # 统一下单 POST
    # http://me.mmiao.click/x_mch/src/views/dev/pay_doc/pay.html
    # 交易金额：默认为人民币交易，单位为分，参数值不能带小数。
    public function orderCreate($amount, $order_no, $type): array
    {
        try {
            $url = "http://pay.mmiao.click/api/pay/create_order";
            switch ($type) {
                case 1:
                    $productId = 8001;
                    break;
                case 2:
                    $productId = 8000;
                    break;
                #其他
                default:
                    throw new Exception('支付类型错误');
            }
            $sendData = [
                'mchId'      => 10045,//商户ID
                'productId'  => $productId,//产品ID
                'mchOrderNo' => $order_no,//商户订单号
                'amount'     => bcmul($amount, 100),//支付金额
                'notifyUrl'  => env('APP_URL') . '/api/callback/pay',//支付结果后台回调URL
            ];
            $key = 'DKQE1UUD3WAHUKQ1GOJM8BS8KKKPJK1AN0U9FAA546C4MTGCZ8HS91UF8WHK6MQ7J0TMKFOOQ2RXESYJUBCXD9G8ZAEC5W5ZXVLNK9CLBX6DT5RCQGVQWM47D5WYUUAL';
            $sendData['sign'] = $this->generateSign($sendData, $key);
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
            if ($result['retCode'] !== 'SUCCESS') {
                throw new ApiException('充值失败，请重试，或者更换渠道');
            }
            return ['payUrl' => $result['payParams']['payUrl'], 'tradeNo' => $result['payOrderId'], 'recharge_id' => 1];
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    function generateSign($data, $keys): string
    {
        // 第一步，按照参数名ASCII码从小到大排序
        ksort($data);

        // 第二步，拼接参数为字符串
        $stringA = '';
        foreach ($data as $key => $value) {
            if ($value !== '') {
                $stringA .= $key . '=' . $value . '&';
            }
        }
        $stringA = rtrim($stringA, '&');

        // 第三步，在stringA最后拼接上key，并进行MD5运算
        $stringSignTemp = $stringA . '&key=' . $keys;

        return strtoupper(md5($stringSignTemp));
    }


    # 查询订单 POST

    /**
     * @throws GuzzleException
     * @throws Exception
     */
    public function orderQuery($order_no): bool
    {
        try {
            $url = "http://pay.mmiao.click/api/pay/query_order";
            $sendData = [
                'mchId'      => 10045,//商户ID
                'mchOrderNo' => $order_no,//商户订单号
            ];
            $key = 'DKQE1UUD3WAHUKQ1GOJM8BS8KKKPJK1AN0U9FAA546C4MTGCZ8HS91UF8WHK6MQ7J0TMKFOOQ2RXESYJUBCXD9G8ZAEC5W5ZXVLNK9CLBX6DT5RCQGVQWM47D5WYUUAL';
            $sendData['sign'] = $this->generateSign($sendData, $key);
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
            if ($result['retCode'] !== 'SUCCESS') {
                throw new ApiException('充值失败，请重试，或者更换渠道');
            }
            if ($result['retMsg']['status'] == 2 || $result['retMsg']['status'] == 3) {
                return true;
            }
            return false;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    # 回调
    /**
     * @throws Exception
     */
    public function callback($params): bool
    {
        try {
            DB::beginTransaction();
            if (empty($params)){
                throw new Exception('参数错误');
            }
            if (empty($params['status'])||empty($params['mchOrderNo'])){
                throw new Exception('参数错误');
            }
            if ($params['status'] == 2 || $params['status'] == 3){
                # 查询订单是否完成
                $resLog = RechargeLog::query()->where('order_no',$params['mchOrderNo'])->first();
                if (empty($resLog)){
                    DB::rollBack();
                    throw new Exception('订单不存在');
                }
                if ($resLog['status'] != 1){
                    DB::commit();
                    return true;
                }
                # 支付完成
                $res = RechargeLog::query()->where('order_no',$params['mchOrderNo'])->update(['status'=>2]);
                if (!$res){
                    DB::rollBack();
                    throw new Exception('更新失败');
                }
                # 增加用户余额
                $runM = UserAccountService::userAccount($resLog->user_id, $resLog->amount, '充值', 7);
                if (!$runM){
                    DB::rollBack();
                    throw new Exception('更新失败');
                }
                DB::commit();
                return true;
            }
            DB::commit();
            return false;
        }catch (Exception $e){
            throw new Exception($e->getMessage());
        }
    }

}
