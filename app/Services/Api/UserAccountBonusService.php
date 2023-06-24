<?php

namespace App\Services\Api;

use App\Exceptions\ApiException;
use App\Models\UserAccountBonus;
use App\Models\Users;
use Exception;

class UserAccountBonusService
{
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
    public static function userAccount(int $user_id, float $profit, string $describe, int $type, int $to_user_id = 0): array
    {
        try {
            // 查询当前明细最后金额
            $total = UserAccountBonus::query()->where(['user_id' => $user_id])->orderBy('id', 'desc')->value('total_balance') ?? 0;
            // 增加/修改 明细金额
            $total_balance = bcadd($total, $profit, 2);#更新后金额
            $createAiLog = [
                'user_id'       => $user_id,
                'old_balance'   => $total,
                'profit'        => $profit,
                'total_balance' => $total_balance,
                'describe'      => $describe,
                'type'          => $type,
                'to_user_id'    => $to_user_id ?? 0
            ];
            $addRes = UserAccountBonus::query()->create($createAiLog);
            //如果添加成功-更新用户表
            if ($addRes) {
                Users::query()->where('id', $user_id)->update(['bonus' => $total_balance]);
            } else {
                throw new ApiException('用户资产明细更新失败');
            }
            return ['code' => 200, 'msg' => '操作成功！'];
        } catch (Exception $e) {
            throw new ApiException($e->getMessage());
        }
    }
}
