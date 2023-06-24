<?php

namespace App\Services\Admin\Pay;

# 支付公共方法
use App\Exceptions\ApiException;
use App\Models\User\Users;
use App\Models\User\UsersAccount;
use App\Models\User\UsersInfo;
use Exception;

class PayCommonService
{
    /**
     * author: Yan
     * DateTime: 2023/3/7
     * Notes: 提现驳回资金操作
     * @param int $withdrawal_id
     * @param $describe
     * @return array
     */
    public static function UserWithAddDetailed(int $withdrawal_id, $describe): array
    {
        // 查询当前提现明细
        $withdrawalData = UsersAccount::query()->where(['withdrawal_id' => $withdrawal_id])->first();
        if (!$withdrawalData) {
            return ['code' => 500, 'msg' => '未查到提现明细'];
        }
        $withdrawalData = $withdrawalData->toArray();
        // 查询当前明细最后金额
        $total = UsersAccount::query()->where(['user_id' => $withdrawalData['user_id']])->orderBy('id', 'desc')->value('total_balance') ?? 0;
        // 增加/修改 明细金额
        $total_balance = bcadd($total, $withdrawalData['profit'], 2);#更新后金额
        $createAiLog = [
            'user_id'       => $withdrawalData['user_id'],
            'old_balance'   => $total,
            'profit'        => $withdrawalData['profit'],
            'total_balance' => $total_balance,
            'type'          => 4,
            'title'         => $describe,
            'activity_id'   => $activity_id ?? null
        ];
        $addRes = UsersAccount::query()->create($createAiLog);
        //扣除冻结金额
        UsersInfo::query()->where('user_id', $withdrawalData['user_id'])->decrement('frozen_balance', $withdrawalData['profit']);
        //如果添加成功-更新用户表
        if ($addRes) {
            UsersInfo::query()->where('user_id', $withdrawalData['user_id'])->update(['money' => $total_balance]);
        } else {
            return ['code' => 500, 'msg' => '用户资产明细更新失败！'];
        }
        return ['code' => 200, 'msg' => '操作成功！'];
    }

    /**
     * User: Yan
     * DateTime: 2023/3/10
     * @param int $user_id 用户id
     * @param float $profit 金额 扣钱为负数 加钱为正数
     * @param string $describe 描述
     * @param int $type 资金类型1:收入  2:支出  3:冻结状态 4:解除冻结 5.邀请收入
     * @return array
     * 用户资金 操作
     * @throws ApiException
     */
    public static function userAccount(int $user_id, float $profit, string $describe, int $type, int $to_user_id = 0): array
    {
        try {
            // 查询当前明细最后金额
            $total = UsersAccount::query()->where(['user_id' => $user_id])->orderBy('id', 'desc')->value('total_balance') ?? 0;
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
            $addRes = UsersAccount::query()->create($createAiLog);
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
