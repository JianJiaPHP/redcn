<?php

namespace App\Http\Controllers\Api;

# 订单管理
use App\Exceptions\ApiException;
use App\Models\Goods;
use App\Models\Recharge;
use App\Models\RechargeLog;
use App\Models\UserAccount;
use App\Models\UserGoods;
use App\Models\UserGoodsLog;
use App\Services\Api\Pay1Service;
use App\Services\Api\Pay2Service;
use App\Services\Api\PayService;
use App\Services\Api\UserAccountService;
use App\Utils\Result;
use Carbon\Carbon;
use DB;
use GuzzleHttp\Exception\GuzzleException;
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

    # 充值渠道
    public function payChannel(): JsonResponse
    {
        $data = Recharge::query()->where('is_open', 1)->select(['id', 'name'])->get();
        return Result::success($data);
    }


    # 充值统一下单

    /**
     * @throws ApiException
     */
    public function payRecharge(): JsonResponse
    {
        $userId = auth('api')->id();
        $lock = Redis::get("payRecharge_" . $userId);
        if ($lock) {
            throw new ApiException("请勿点击太频繁，请稍后再试！");
        }
        # 加锁
        Redis::set("payRecharge_" . $userId, 1, 'EX', 3, 'NX');
        try {
            $params = request()->all();
            # 验证器
            $validator = validator(request()->all(), [
                'recharge_id' => 'required',
                # 金额 最多2位小数 不能为负数
                'amount'      => 'required|numeric|regex:/^[0-9]+(.[0-9]{1,2})?$/',
                'type'        => 'required|in:1,2'
            ], [
                'recharge_id.required' => '充值渠道不能为空',
                'amount.required'      => '充值金额不能为空',
                'amount.numeric'       => '充值金额必须为数字',
                'amount.regex'         => '充值金额最多2位小数',
                'type.required'        => '充值类型不能为空',
                'type.in'              => '充值类型错误',
            ]);
            if ($validator->fails()) {
                Redis::del("payRecharge_" . $userId);
                return Result::fail($validator->errors()->first());
            }
            # 查询充值渠道
            $rechargeGo = Recharge::query()->where('id', $params['recharge_id'])->value('go');
            # 生成订单号
            $order_no = $this->createOrderNo();

            switch ($rechargeGo) {
                case 1:
                    $serve = new PayService();
                    try {
                        $res = $serve->orderCreate($params['amount'], $order_no, $params['type']);
                    } catch (GuzzleException $e) {
                        Redis::del("payRecharge_" . $userId);
                        throw new ApiException($e->getMessage());
                    }
                    break;
                case 2:
                    $serve = new Pay1Service();
                    try {
                        $res = $serve->orderCreate($params['amount'], $order_no, $params['type']);
                    } catch (GuzzleException $e) {
                        Redis::del("payRecharge_" . $userId);
                        throw new ApiException($e->getMessage());
                    }
                    break;
                case 3:
                    $serve = new Pay2Service();
                    try {
                        $res = $serve->orderCreate($params['amount'], $order_no, $params['type']);
                    } catch (GuzzleException $e) {
                        Redis::del("payRecharge_" . $userId);
                        throw new ApiException($e->getMessage());
                    }
                    break;
                default:
                    Redis::del("payRecharge_" . $userId);
                    throw new ApiException("充值渠道不存在");
            }
            if (empty($res)) {
                Redis::del("payRecharge_" . $userId);
                throw new ApiException("充值失败");
            }
            # 创建支付订单
            $rs = RechargeLog::query()->create([
                'user_id'     => $userId,
                'order_no'    => $order_no,
                'amount'      => $params['amount'],
                'zhifu_no'    => $res['tradeNo'],
                'status'      => 1,
                'type'        => $params['type'],
                'recharge_id' => $res['recharge_id']
            ]);
            if (!$rs) {
                Redis::del("payRecharge_" . $userId);
                throw new ApiException("充值失败");
            }
            Redis::del("payRecharge_" . $userId);
            return Result::success($res);
        } catch (\Exception $e) {
            #删锁
            Redis::del("payRecharge_" . $userId);
            return Result::fail($e->getMessage());
        }
    }

    # 生成订单号
    public function createOrderNo(): string
    {
        $order_no = 'ddd' . rand(100000, 999999) . time();
        # 查询订单号是否有重复
        $orderInfo = RechargeLog::query()->where('order_no', $order_no)->exists();
        if ($orderInfo) {
            $this->createOrderNo();
        }
        return $order_no;
    }

    # 查询充值订单状态
    public function queryRecharge(): JsonResponse
    {
        try {
            $params = request()->all();
            # 验证器
            $validator = validator(request()->all(), [
                'order_no' => 'required',
            ], [
                'order_no.required' => '订单号不能为空',
            ]);
            if ($validator->fails()) {
                return Result::fail($validator->errors()->first());
            }
            $userId = auth('api')->id();
            # 查询订单
            $orderInfo = RechargeLog::query()
                ->where('zhifu_no', $params['order_no'])
                ->where('user_id', $userId)
                ->select(['id', 'recharge_id', 'status'])
                ->first();
            if (!$orderInfo) {
                return Result::fail("订单不存在");
            }
            if ($orderInfo['status'] == 1) {
                # 查询支付渠道
                $res = false;
                switch ($orderInfo['recharge_id']) {
                    case 1:
                        $serve = new PayService();
                        try {
                            $res = $serve->orderQuery($params['order_no']);
                        } catch (GuzzleException $e) {
                            throw new ApiException($e->getMessage());
                        }
                        break;
                    case 2:
                        $serve = new Pay1Service();
                        try {
                            $res = $serve->orderQuery($params['order_no']);
                        } catch (GuzzleException $e) {
                            throw new ApiException($e->getMessage());
                        }
                        break;
                    case 3:
                        $serve = new Pay2Service();
                        try {
                            $res = $serve->orderQuery($params['order_no']);
                        } catch (GuzzleException $e) {
                            throw new ApiException($e->getMessage());
                        }
                        break;
                    default:
                        throw new ApiException("充值渠道不存在");
                }
                if ($res) {
                    RechargeLog::query()
                        ->where('zhifu_no', $params['order_no'])
                        ->where('user_id', $userId)
                        ->update(['status' => 2]);
                    return Result::success(2);
                }
            }
            return Result::success($orderInfo['status']);
        } catch (\Exception $e) {
            return Result::fail($e->getMessage());
        }

    }


    # 通道1回调
    public function callbackPay()
    {
        try {
            $params = request()->all();
            \Log::info('通道1回调参数', $params);
            $model = new PayService();
            $res = $model->callback($params);
            if ($res) {
                return 'success';
            }
            return 'fail';
        } catch (\Exception $e) {
            return Result::fail($e->getMessage());
        }
    }
    # 通道二回调
    public function callbackPay1()
    {
        try {
            $params = request()->all();
            \Log::info('通道2回调参数', $params);
            $model = new Pay1Service();
            $res = $model->callback($params);
            if ($res) {
                return 'SUCCESS';
            }
            return 'FAIL';
        } catch (\Exception $e) {
            return Result::fail($e->getMessage());
        }
    }

    # 通道三回调
    public function callbackPay2()
    {
        try {
            $params = request()->all();
            \Log::info('通道3回调参数', $params);
            $model = new Pay2Service();
            $res = $model->callback($params);
            if ($res) {
                return 'OK';
            }
            return 'fail';
        } catch (\Exception $e) {
            return Result::fail($e->getMessage());
        }
    }
}
