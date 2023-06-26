<?php

namespace App\Http\Controllers\Api;

# 收益管理
use App\Models\UserAccountBonus;
use App\Utils\Result;

class IncomeController
{
    # 赠送金额页面数据
    public function bonusData(): \Illuminate\Http\JsonResponse
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
}
