<?php

namespace App\Http\Controllers\Api;

# 收益管理
use App\Exceptions\ApiException;
use App\Models\AccumulateConfig;
use App\Models\PayOrder;
use App\Models\UserAccount;
use App\Models\UserAccountBonus;
use App\Models\Users;
use App\Models\Withdrawals;
use App\Services\Api\UserAccountBonusService;
use App\Services\Api\UserAccountService;
use App\Utils\Result;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Redis;

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
        $list = UserAccount::query()->where('user_id', $userId)->orderByDesc('id')
            ->select(['id', 'user_id', 'old_balance', 'profit', 'total_balance', 'type', 'describe', 'created_at'])
            ->paginate(request()->query('limit', 15));
        # 当前剩余余额
        $totalBalance = UserAccount::query()->where('user_id', $userId)->orderByDesc('id')->value('total_balance');
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
        $list = UserAccountBonus::query()->where('user_id', $userId)->orderByDesc('id')
            ->select(['id', 'user_id', 'old_balance', 'profit', 'total_balance', 'type', 'describe', 'created_at'])
            ->paginate(request()->query('limit', 15));
        # 当前剩余余额
        $totalBalance = UserAccountBonus::query()->where('user_id', $userId)->orderByDesc('id')->value('total_balance');
        return Result::success(
            [
                'list'         => $list,#列表
                'totalBalance' => $totalBalance,#当前剩余余额
            ]
        );
    }


    # 余额提现页数据
    public function withdrawData(): JsonResponse
    {
        $userId = auth('api')->id();
        # 当前剩余余额
        $totalBalance = UserAccount::query()->where('user_id', $userId)->orderByDesc('id')->value('total_balance') ?? 0;
        # 累计产品收益
        $toGoodsIncome = UserAccount::query()->where('user_id', $userId)->where('type', 2)->orderByDesc('id')->sum('profit');
        # 累计业绩金额
        $totalIncome = UserAccount::query()->where('user_id', $userId)->where('type', 3)->orderByDesc('id')->sum('profit');
        return Result::success(
            [
                'totalBalance'  => $totalBalance,#当前剩余余额
                'totalIncome'   => $totalIncome,#累计收益
                'toGoodsIncome' => $toGoodsIncome#累计产品收益
            ]
        );
    }

    # 发起余额提现
    public function createWithdraw(): JsonResponse
    {
        $userId = auth('api')->id();
        $params = request()->all();
        # 验证
        $validator = validator($params, [
            'amount'          => 'required|numeric|min:1',
            'card_number'     => 'required',
            'cardholder_name' => 'required',
        ], [
            'amount.required'          => '提现金额不能为空',
            'amount.numeric'           => '提现金额必须是数字',
            'amount.min'               => '提现金额不能小于1',
            'card_number.required'     => '银行卡号不能为空',
            'cardholder_name.required' => '持卡人姓名不能为空',
        ]);
        if ($validator->fails()) {
            return Result::fail($validator->errors()->first());
        }
        # 查询金额是否足够
        $totalBalance = UserAccount::query()->where('user_id', $userId)->orderByDesc('id')->value('total_balance') ?? 0;
        if ($totalBalance < $params['amount']) {
            return Result::fail('余额不足');
        }
        try {
            DB::beginTransaction();
            # 生成提现数据
            $withdrawData = [
                'user_id'         => $userId,
                'amount'          => $params['amount'],
                'card_number'     => $params['card_number'],
                'cardholder_name' => $params['cardholder_name'],
                'status'          => 1,
                'remark'          => '余额钱包提现'
            ];
            $withdraw = Withdrawals::query()->create($withdrawData);
            if (!$withdraw) {
                DB::rollBack();
                throw new \Exception('提现失败');
            }
            # 扣除金额
            UserAccountService::userAccount($userId, -$params['amount'], '余额钱包提现', 6);
            DB::commit();
            return Result::success('提现成功');
        } catch (\Exception $e) {
            return Result::fail($e->getMessage());
        }
    }


    # 余额钱包提现明细
    public function withdrawList(): JsonResponse
    {
        $userId = auth('api')->id();
        $list = Withdrawals::query()->where('user_id', $userId)->orderByDesc('id')->get();
        return Result::success($list);
    }


    # 奖励钱包提现页数据
    public function withdrawBonusData(): JsonResponse
    {
        $userId = auth('api')->id();
        # 当前剩余余额
        $totalBalance = UserAccountBonus::query()->where('user_id', $userId)->orderByDesc('id')->value('total_balance') ?? 0;
        # 累计邀请奖励
        $toGoodsIncome = UserAccountBonus::query()->where('user_id', $userId)->where('type', 1)->orderByDesc('id')->sum('profit');
        # 累计赠送金额
        $totalIncome = UserAccountBonus::query()->where('user_id', $userId)->where('type', 2)->orderByDesc('id')->sum('profit');
        return Result::success(
            [
                'totalBalance'  => $totalBalance,#当前剩余余额
                'totalIncome'   => $totalIncome,#累计赠送金额
                'toGoodsIncome' => $toGoodsIncome#累计邀请奖励
            ]
        );
    }

    # 奖金钱包日收益列表
    public function bonusList(): JsonResponse
    {
        $userId = auth('api')->id();
        #奖金钱包日收益列表 按日期来计算每天收益金额
        $list = UserAccountBonus::query()
            ->select(DB::raw('DATE(created_at) as date, SUM(profit) as daily_profit'))
            ->where('user_id', $userId)
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderByDesc('id')
            ->get();
        return Result::success($list);
    }
    # 余额钱包日收益列表
    public function balanceList(): JsonResponse
    {
        $userId = auth('api')->id();
        #奖金钱包日收益列表 按日期来计算每天收益金额
        $list = UserAccount::query()
            ->select(DB::raw('DATE(created_at) as date, SUM(profit) as daily_profit'))
            ->where('user_id', $userId)
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderByDesc('id')
            ->get();
        return Result::success($list);
    }


    # 邀请奖励领取

    /**
     * @throws ApiException
     */
    public function bonusReceive(): JsonResponse
    {
        $userId = auth('api')->id();
        // 尝试获取锁
        $lockAcquired = Redis::get("bonusReceive" . $userId);
        if ($lockAcquired){
            throw new ApiException("领取失败！请重试");
        }
        Redis::set("goodsReceive" . $userId, 1, 'EX', 10, 'NX');
        # 查询上级邀请人邀请了多少人了 根据邀请条件给予奖励
        $count = Users::query()->where('p_id', $userId)->count();
        # 查询user领取到了第几阶段
        $bJie = Users::query()->where('id', $userId)->value('b_jie');
        $bJie = $bJie + 1;
        # 查询邀请奖励配置
        $accumulateConfig = AccumulateConfig::query()->where('type', 2)->where('jieduan', $bJie)->first();
        if ($accumulateConfig) {
            if ($accumulateConfig['num'] <= $count) {
                # 领取奖励
                try {
                    UserAccountService::userAccount($userId, $accumulateConfig['value'], '邀请新用户累计达到' . $accumulateConfig['num'] . '人奖励', 4);
                } catch (ApiException $e) {
                    return Result::fail($e->getMessage());
                }
                # 更新user表
                Users::query()->where('id', $userId)->update(['b_jie' => $bJie]);
                # 删除锁
                Redis::del("bonusReceive" . $userId);
                return Result::success('领取成功');
            }
        }
        Redis::del("bonusReceive" . $userId);
        return Result::fail('未达到领取条件');
    }

    # 业绩奖励领取

    /**
     * @throws ApiException
     */
    public function goodsReceive(): JsonResponse
    {
        $userId = auth('api')->id();
        // 尝试获取锁
        $lockAcquired = Redis::get("goodsReceive" . $userId);
        if ($lockAcquired){
            throw new ApiException("领取失败！请重试");
        }
        Redis::set("goodsReceive" . $userId, 1, 'EX', 10, 'NX');

        # 查询上级邀请人邀请了多少人了 根据邀请条件给予奖励
        $count = Users::query()->where('p_id', $userId)->pluck('id');
        # 查询我的下级 购买产品的金额
        $total = PayOrder::query()->whereIn('user_id', $count)->where('pay_status', 3)->sum('total_amount');
        # 查询user领取到了第几阶段
        $aJie = Users::query()->where('id', $userId)->value('a_jie');
        $aJie = $aJie + 1;
        # 查询邀请奖励配置
        $accumulateConfig = AccumulateConfig::query()->where('type', 2)->where('jieduan', $aJie)->first();
        if ($accumulateConfig) {
            if ($accumulateConfig['num'] <= $total) {
                # 领取奖励
                try {
                    UserAccountService::userAccount($userId, $accumulateConfig['value'], '业绩累计达到' . $accumulateConfig['num'] . '奖励', 3);
                } catch (ApiException $e) {
                    return Result::fail($e->getMessage());
                }
                # 更新user表
                Users::query()->where('id', $userId)->update(['aJie' => $aJie]);
                # 删除锁
                Redis::del("goodsReceive" . $userId);
                return Result::success('领取成功');
            }
        }
        # 删除锁
        Redis::del("goodsReceive" . $userId);
        return Result::fail('未达到领取条件');
    }


}
