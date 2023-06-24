<?php

namespace App\Services\Admin\Pay;

use App\Models\Pay\PayOrder;
use App\Models\Store\Store;
use App\Models\Store\StorePointLog;
use App\Models\User\Users;
use App\Models\Withdrawal\Withdrawal;
use DB;
use Exception;

/**
 * 支付回调方法 业务处理层
 */
class PayNotifyService
{

    /**
     * User: Yan
     * DateTime: 2023/3/10
     * 支付宝成功回调处理方法
     * @throws Exception
     */
    public function PointPayNotify($result): array
    {
        try {
            DB::beginTransaction();
            //查询商户信息
            $store_id = PayOrder::query()->where(['order_no' => $result['out_trade_no']])->value('store_id');
            if ($store_id == 0) {
                throw new Exception('未查到商户信息');
            }
            //查询当前明细最后金额
            $total = StorePointLog::query()->where(['store_id' => $store_id])->orderBy('id', 'desc')->value('total_balance') ?? 0;
            //增加明细表记录
            \Log::info('商户id:' . $store_id . '购买代币充值套餐,增加金额:' . $result['total_amount'] . '元,更新后金额:' . bcadd($total, $result['total_amount'], 2) . '元');
            $total_balance = bcadd($total, $result['total_amount'], 2);#更新后金额
            $createAiLog = [
                'store_id'      => $store_id,
                'type'          => 1,
                'old_balance'   => $total,
                'profit'        => $result['total_amount'],
                'total_balance' => $total_balance,
                'describe'      => '购买代币充值套餐',
            ];
            $addRes = StorePointLog::query()->create($createAiLog);
            //如果添加成功-更新商户表
            if ($addRes) {
                $storeUpdate = Store::query()->where('id', $store_id)->update(['money' => $total_balance]);
                if (!$storeUpdate) {
                    DB::rollBack();
                    throw new Exception('商户信息更新失败！');
                }
            } else {
                DB::rollBack();
                throw new Exception('商户明细更新失败！');
            }
            DB::commit();
            return ['code' => 200, 'msg' => '回调成功！'];
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage());
        }

    }


    /**
     * 处理提现转账
     * User:Deng
     * DateTime: 2023/3/10
     */
    public function WithdrawalTransferOut($result): array
    {
        DB::beginTransaction();
        //查询商户信息
        $order_no = PayOrder::query()->where(['order_no' => $result['out_trade_no']])->value('order_no');
        if (empty($order_no)) {
            return ['code' => 500, 'msg' => '未查到商户信息'];
        }
        #核实提现记录
        $withdrawal_info = Withdrawal::query()->where(['withdrawal_no' => $order_no, 'is_appropriation' => 1])->select('id', 'user_id', 'withdrawal_price');
        if (empty($withdrawal_info)) {
            return ['code' => 500, 'msg' => '未查到提现订单信息'];
        }
        $update_user_balance = Users::query()->where(['user_id' => $withdrawal_info['user_id']])->where('frozen_balance', '=>', $withdrawal_info['withdrawal_price'])->decrement('frozen_balance', $withdrawal_info['withdrawal_price']);
        if (empty($update_user_balance)) {
            DB::rollBack();
            return ['code' => 500, 'msg' => '冻结金额更新失败'];
        }
        $update_order_status = Withdrawal::query()->where(['id' => $withdrawal_info['id']])->update(array('is_appropriation' => 2));
        if (empty($update_order_status)) {
            DB::rollBack();
            return ['code' => 500, 'msg' => '更新提现记录失败'];
        }

        DB::commit();
        return ['code' => 200, 'msg' => '回调成功！'];
    }
}
