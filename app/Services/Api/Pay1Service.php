<?php

namespace App\Services\Api;

use App\Exceptions\ApiException;
use App\Http\Controllers\Api\OrderController;
use App\Models\RechargeLog;
use DB;
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
                case 1:
                    $productId = '203';
                    break;
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
                'amount'     => bcmul($amount,100),//支付金额
                'extParam'   => $order_no,//支付金额
                'notifyUrl'  => env('APP_URL') . '/api/callback/pay1',//支付结果后台回调URL
                'returnUrl'  => env('APP_URL') . '/api/callback/pay1',//支付结果后台回调URL
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
            if ($result['code'] !== 0) {
                throw new ApiException('充值失败，请重试，或者更换渠道');
            }
            return ['payUrl' => $result['data']['payUrl'], 'tradeNo' => $result['data']['tradeNo'], 'recharge_id' => 2];
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
     * @throws GuzzleException
     * @throws Exception
     */
    public function orderQuery($order_no): bool
    {
        try {
            $url = "https://anyipayih52ioq8d.zzbbm.xyz/api/pay/query";
            $sendData = [
                'mchId'      => 'M1687593174',//商户ID
                'outTradeNo' => $order_no,//产品ID
                'reqTime'    => time(),
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
            if ($result['code'] != '0') {
                throw new ApiException('充值失败，请重试，或者更换渠道');
            }
            if ($result['data']['state'] == '1'){
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
            if (empty($params['state'])||empty($params['outTradeNo'])){
                throw new Exception('参数错误');
            }
            if ($params['state'] == 1){
                # 查询订单是否完成
                $resLog = RechargeLog::query()->where('order_no',$params['outTradeNo'])->first();
                if (empty($resLog)){
                    DB::rollBack();
                    throw new Exception('订单不存在');
                }
                if ($resLog['status'] != 1){
                    DB::commit();
                    return true;
                }
                # 支付完成
                $res = RechargeLog::query()->where('order_no',$params['outTradeNo'])->update(['status'=>2]);
                if (!$res){
                    DB::rollBack();
                    throw new Exception('更新失败');
                }
                if ($resLog['pay_type'] == 2){
                    $new = new OrderController();
                    $is = $new->okGoods($resLog);
                    if (!$is){
                        DB::rollBack();
                        throw new Exception('更新失败');
                    }
                }else{
                    # 增加用户余额
                    $runM = UserAccountService::userAccount($resLog->user_id, $resLog->amount, '充值', 7);
                    if (!$runM){
                        DB::rollBack();
                        throw new Exception('更新失败');
                    }
                }
                DB::commit();
                return true;
            }elseif($params['state'] == 2){
                $res = RechargeLog::query()->where('order_no',$params['outTradeNo'])->update(['status'=>3]);
                if (!$res){
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
