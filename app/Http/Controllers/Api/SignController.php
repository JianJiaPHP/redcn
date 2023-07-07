<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Config;
use App\Models\SignLog;
use App\Models\Users;
use App\Utils\Result;
use Carbon\Carbon;
use DB;
use Exception;
use Illuminate\Http\JsonResponse;

class SignController extends Controller
{

    /**
     * Notes: 获取当前签到数据
     */
    public function getSignData(): JsonResponse
    {
        $userId = auth('api')->id();

        $signIns = SignLog::query()
            ->where('user_id', $userId)
            ->orderBy('created_at', 'asc')
            ->pluck('created_at');
        # 连续签到天数
        $consecutiveDays = $this->countSign($signIns);

        # 查询当月记录
        $month = SignLog::query()
            ->where('user_id',$userId)
            ->whereMonth('created_at', '=', date('m'))
            ->whereYear('created_at', '=', date('Y'))
            ->pluck('created_at');

        # 当前用户拥有的红旗数量
        $userRedFlagSum = Users::query()->where('id',$userId)->value('sign_sum');
        $redFlagValue = 0;
        if ($userRedFlagSum > 0) {
            # 红旗当前价值
            $redFlagValue = ((new Config())->getAll()['sign.value'] * $userRedFlagSum) ?? 0;
        }
        # 查询当日有没有签到
        $isSignIn = SignLog::query()
            ->where('user_id', $userId)
            ->whereDate('created_at', Carbon::today())
            ->exists();

        return Result::success(
            [
                'consecutiveDays' => $consecutiveDays,# 连续签到天数
                'userRedFlagSum'  => $userRedFlagSum,# 拥有红旗数量
                'redFlagValue'    => $redFlagValue,# 红旗价值
                'monthSign'      => $month,# 当月签到日期
                'isSignIn'      => $isSignIn,# 当日是否签到
                'user_id'=>$userId
            ]
        );
    }


    /**
     * Notes: 用户签到
     */
    public function userSign(): JsonResponse
    {
        $userId = auth('api')->id();

        # 判断用户当天是否签到
        $isSignIn = SignLog::query()
            ->where('user_id', $userId)
            ->whereDate('created_at', Carbon::today())
            ->exists();
        if ($isSignIn){
            return Result::fail('今日已签到');
        }

        $signIns = SignLog::query()
            ->where('user_id', $userId)
            ->orderBy('created_at', 'asc')
            ->pluck('created_at');
        $consecutiveDays = $this->countSign($signIns);
        # 大于14 后 每5的倍数则多赠送5面
        $redFlagSum = 1;
        $consecutiveDays = $consecutiveDays + 1;
        if ($consecutiveDays >= 14 && $consecutiveDays % 5 === 0) {
            $redFlagSum = 6;
        }
        try {
            DB::beginTransaction();
            # 增加用户表的红旗数量
            # 查询我所有的红旗
            $userRed = Users::query()->where('id',$userId)->increment('sign_sum',$redFlagSum);
            if (!$userRed){
                DB::rollBack();
                throw new Exception('增加用户红旗数量失败');
            }
            # 增加签到记录
            $signLog = SignLog::query()->create(['user_id' => $userId,'value'=>$redFlagSum]);
            if (!$signLog){
                DB::rollBack();
                throw new Exception('增加签到记录失败');
            }
            DB::commit();
            return Result::success('签到成功');
        }catch (Exception $exception){
            return Result::fail($exception->getMessage());
        }


    }


    /**
     * Notes: 查询连续签到次数
     */
    public function countSign($signIns): int
    {
        $consecutiveDays = 0;
        $previousDate = null;
        foreach ($signIns as $signIn) {
            $signIn = Carbon::parse($signIn)->toDateString();
            $currentDate = Carbon::parse($signIn);
            if ($previousDate && $currentDate->diffInDays($previousDate) === 1) {
                // 当前日期与前一天相差1天，表示连续签到
                $consecutiveDays++;
            } elseif (!$previousDate) {
                // 第一次循环，直接加1天
                $consecutiveDays++;
            } else {
                // 断签，重置连续天数
                $consecutiveDays = 0;
            }

            $previousDate = $currentDate;
        }
        if ($consecutiveDays > 0) {
            $consecutiveDays += 1;
        }
        return $consecutiveDays;
    }

}
