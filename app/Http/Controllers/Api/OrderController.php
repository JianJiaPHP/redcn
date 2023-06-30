<?php

namespace App\Http\Controllers\Api;

# 订单管理
use App\Exceptions\ApiException;
use App\Models\Goods;
use App\Models\UserAccount;
use App\Models\UserGoods;
use App\Models\UserGoodsLog;
use App\Services\Api\UserAccountService;
use App\Utils\Result;
use Carbon\Carbon;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Redis;

class OrderController
{

    # 产品购买记录
    public function payLog(): JsonResponse
    {
        $userId = auth('api')->id();
        $where['user_id'] = $userId;
        $where[] = ['end_date', '>=', Carbon::now()->toDateTimeString()];
        # 持有产品列表
        $userGoodsData = UserGoods::query()
            ->where($where)
            ->orderByDesc('id')->select(['id', 'user_id', 'goods_id', 'status', 'type', 'name', 'amount', 'income', 'end_date'])->get();
        foreach ($userGoodsData as &$value) {
            # 查询昨日收益
            $value['yesterday_income'] = UserGoodsLog::query()->where('user_goods_id', $value['id'])
                ->whereDate('date', Carbon::yesterday())->sum('income') ?? 0;
            # 查询持有收益
            $value['hold_income'] = UserGoodsLog::query()->where('user_goods_id', $value['id'])->sum('income');
            # 查询持有收益率
            $value['hold_income_rate'] = bcdiv($value['hold_income'], $value['amount'], 2);
        }
        return Result::success($userGoodsData);
    }

    # 指定产品收益明细
    public function incomeLog(): JsonResponse
    {
        $params = request()->all();
        # 验证
        $validator = validator($params, [
            'user_goods_id' => 'required',
        ], [
            'user_goods_id.required' => '产品ID不能为空',
        ]);
        if ($validator->fails()) {
            return Result::fail($validator->errors()->first());
        }
        $userId = auth('api')->id();
        # 查询持有收益
        $hold_income = UserGoodsLog::query()->where('user_goods_id', $params['user_goods_id'])
            ->where('user_id', $userId)->sum('income');
        # 查询收益明细
        $userGoodsLogData = UserGoodsLog::query()->where('user_goods_id', $params['user_goods_id'])
            ->where('user_id', $userId)
            ->orderByDesc('id')->get();
        return Result::success(['userGoodsData' => $hold_income, 'userGoodsLogData' => $userGoodsLogData]);
    }


    # 购买商品

    /**
     * @throws ApiException
     */
    public function payGoods(): JsonResponse
    {
        #查询有没有redis锁
        $userId = auth('api')->id();
        $lock = Redis::get("payGoods_" . $userId);
        if ($lock) {
            throw new ApiException("请勿点击太频繁，请稍后再试！");
        }
        # 加锁
        Redis::set("payGoods_" . $userId, 1, 'EX', 3, 'NX');
        $params = request()->all();
        # 验证器
        $validator = validator($params, [
            'goods_id' => 'required',
            'number'   => 'required',
        ], [
            'goods_id.required' => '商品ID不能为空',
            'number.required'   => '购买数量不能为空',
        ]);
        if ($validator->fails()) {
            return Result::fail($validator->errors()->first());
        }
        # 查询商品单价
        $goodsInfo = Goods::query()->where('id', $params['goods_id'])->first();
        # 计算总价
        $amount = bcmul($goodsInfo['amount'], $params['number'], 2);
        # 查询用户余额
        $userAmount = UserAccount::query()->where('user_id', $userId)->orderByDesc('id')->value('total_balance');
        if ($userAmount < $amount) {
            Redis::del("payGoods_" . $userId);
            return response()->json([
                'code'    => 501,
                'message' => '余额不足',
                'data'    => ''
            ]);
        }
        try {
            DB::beginTransaction();
            # 扣除用户余额
            UserAccountService::userAccount($userId, -$amount, '购买产品', 5);
            # 增加用户产品信息
            $res = UserGoods::query()->create([
                'user_id'      => $userId,
                'goods_id'     => $params['goods_id'],
                'status'       => 1,
                'type'         => $goodsInfo['type'],
                'name'         => $goodsInfo['name'],
                'amount'       => $amount,
                'introduce'    => $goodsInfo['introduce'],
                'income'       => bcmul($goodsInfo['income'], $params['number'], 2),
                'validity_day' => $goodsInfo['validity_day'],
                'end_rewards'  => bcmul($goodsInfo['end_rewards'], $params['number'], 2),
                'start_date'   => Carbon::now()->toDateTimeString(),
                'end_date'     => Carbon::now()->addDays($goodsInfo['validity_day'])->toDateTimeString(),
            ]);
            if (!$res) {
                DB::rollBack();
                throw new ApiException("购买失败");
            }
            Redis::del("payGoods_" . $userId);
            DB::commit();
            return Result::success("购买成功");
        } catch (\Exception $e) {
            # 删除锁
            Redis::del("payGoods_" . $userId);
            return Result::fail($e->getMessage());
        }


    }
}
