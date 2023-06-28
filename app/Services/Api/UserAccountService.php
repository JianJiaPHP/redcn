<?php

namespace App\Services\Api;

use App\Exceptions\ApiException;
use App\Models\UserAccount;
use App\Models\UserGoodsLog;
use App\Models\Users;
use Exception;

class UserAccountService
{

    public static function productIncome($params)
    {
        try {
            $userId = $params['user_id'];
            $id = $params['id'];
            $income = $params['income'];
            $toDay = date('Y-m-d');
            # 查询该用户改产品今日记录
            $exits = UserGoodsLog::query()->where(['user_id' => $userId, 'user_goods_id' => $id, 'date' => $toDay])->exists();
            if ($exits) {
                \Log::error('产品定时收益异常：1');
                return '';
            }
            # 添加记录
            $add = UserGoodsLog::query()->create(['user_id' => $userId, 'user_goods_id' => $id, 'income' => $income, 'date' => $toDay]);
            if (!$add) {
                \Log::error('产品定时收益异常：2');

                return '';
            }
            self::userAccount($userId, $income, '产品定时收益', 2);
            if ($toDay == date('Y-m-d',$params['end_date'])){
                # 最后一天收益奖励
                self::userAccount($userId, $params['end_rewards'], '产品最后一天收益奖励定时收益', 2);
            }
            return ['code' => 200, 'msg' => '操作成功！'];
        } catch (Exception $e) {
            \Log::error('产品定时收益异常：' . $e->getMessage());
        }
    }

    # 产品定时收益

    /**
     * User: Yan
     * @param int $user_id 用户id
     * @param float $profit 金额 扣钱为负数 加钱为正数
     * @param string $describe 描述
     * @param int $type 资金类型1.邀请奖励\r\n2.邀请赠送金额3：小红旗兑换
     * @return array
     * 用户资金 操作
     * @throws ApiException
     */
    public static function userAccount(int $user_id, float $profit, string $describe, int $type): array
    {
        try {
            // 查询当前明细最后金额
            $total = UserAccount::query()->where(['user_id' => $user_id])->orderBy('id', 'desc')->value('total_balance') ?? 0;
            // 增加/修改 明细金额
            $total_balance = bcadd($total, $profit, 2);#更新后金额
            $createAiLog = [
                'user_id'       => $user_id,
                'old_balance'   => $total,
                'profit'        => $profit,
                'total_balance' => $total_balance,
                'describe'      => $describe,
                'type'          => $type,
            ];
            $addRes = UserAccount::query()->create($createAiLog);
            //如果添加成功-更新用户表
            if ($addRes) {
                Users::query()->where('id', $user_id)->update(['balance' => $total_balance]);
            } else {
                throw new ApiException('用户资产明细更新失败');
            }
            return ['code' => 200, 'msg' => '操作成功！'];
        } catch (Exception $e) {
            throw new ApiException($e->getMessage());
        }
    }
}
