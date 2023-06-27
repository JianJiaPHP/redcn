<?php

namespace App\Http\Controllers\Api;

# 收益管理
use App\Models\UserAccount;
use App\Models\UserAccountBonus;
use App\Utils\Result;
use Illuminate\Http\JsonResponse;

class IncomeController
{
    # 赠送金额页面数据
    public function bonusData(): JsonResponse
    {
        $userId = auth('api')->id();
        # 总金额
        $total = UserAccountBonus::query()->where('user_id', $userId)->orderBy('id', 'desc')->value('total_balance');
        # 赠送金额
        $bonus = UserAccountBonus::query()->where('user_id', $userId)->where(['type' => 2])->sum('profit');
        # 奖励金额
        $reward = UserAccountBonus::query()->where('user_id', $userId)->where(['type' => 1])->sum('profit');
        # 待提现金额
        $withdraw = 0;
        return Result::success(
            [
                'total'    => $total,#总金额
                'bonus'    => $bonus,#赠送金额
                'reward'   => $reward,#奖励金额
                'withdraw' => $withdraw,#待提现金额
            ]
        );
    }

    # 我的余额流水记录
    public function userAccountList(): JsonResponse
    {
        $userId = auth('api')->id();
        $list = UserAccount::query()->where('user_id',$userId)->orderByDesc('id')
            ->select(['id','user_id','old_balance','profit','total_balance','type','describe','created_at'])
            ->paginate(request()->query('limit', 15));
        # 当前剩余余额
        $totalBalance = UserAccount::query()->where('user_id',$userId)->orderByDesc('id')->value('total_balance');
        return Result::success(
            [
                'list'         => $list,#列表
                'totalBalance' => $totalBalance,#当前剩余余额
            ]
        );
    }
    # 奖金钱包流水记录
    public function userAccountBonusList(): JsonResponse
    {
        $userId = auth('api')->id();
        $list = UserAccountBonus::query()->where('user_id',$userId)->orderByDesc('id')
            ->select(['id','user_id','old_balance','profit','total_balance','type','describe','created_at'])
            ->paginate(request()->query('limit', 15));
        # 当前剩余余额
        $totalBalance = UserAccountBonus::query()->where('user_id',$userId)->orderByDesc('id')->value('total_balance');
        return Result::success(
            [
                'list'         => $list,#列表
                'totalBalance' => $totalBalance,#当前剩余余额
            ]
        );
    }
}
