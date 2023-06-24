<?php

namespace App\Services\Api;

use App\Exceptions\ApiException;
use App\Models\Base\Config;
use App\Models\chat\Goods;
use App\Models\chat\Subscribe;
use App\Models\Pay\PayOrder;
use App\Models\User\Users;
use App\Models\User\UsersAccount;
use App\Models\Withdrawal\Withdrawal;
use App\Services\Admin\Pay\PayCommonService;
use App\Utils\Alipay;
use App\Utils\WeChatPay;
use Carbon\Carbon;
use DB;
use Exception;
use Yansongda\Pay\Pay;

class OrderService
{

    /**
     * User: Yan
     * DateTime: 2023/5/11
     * 加入社群支付成功
     * @param int $userId 用户id
     * @param string $orderNo 订单号
     * @return bool
     * @throws Exception
     * @throws Exception
     */
    public function membershipPay(int $userId, string $orderNo): bool
    {
        try {
            # 修改用户状态
            DB::beginTransaction();
            $user = Users::query()->where('id', $userId)->update(['is_fellow' => 1]);
            if (!$user) {
                DB::rollBack();
                throw new Exception("修改用户状态失败");
            }
            DB::commit();
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * @param int $userId
     * @param string $orderNo
     * @return void
     * @Time 2023/5/19 17:28
     * @author sunsgne
     */
    public function handleWithdrawState(int $userId, string $orderNo)
    {

        Withdrawal::query()->where(['withdrawal_no' => $orderNo, 'user_id' => $userId])->update([]);
    }


    /**
     * User: Yan
     * DateTime: 2023/5/11
     * @param int $userId
     * @param string $orderNo
     * @return bool
     * @throws Exception
     * Gpt购买成功
     * 修改订单状态 - 需要改/新增订阅
     */
    public function gptPay(int $userId, string $orderNo): bool
    {
        try {
            DB::beginTransaction();
            # 查询订单
            $order = PayOrder::query()->where(['user_id' => $userId, 'order_no' => $orderNo])->first();
            if (!$order) {
                DB::rollBack();
                throw new Exception("订单不存在");
            }
            # 判断是否有享学豆消费
            if ($order['discount'] > 0) {
                # 走资金表 扣除享学豆
                $payRes = PayCommonService::userAccount($userId, ($order['discount'] * -1), '购买商品抵扣', 2);
                if ($payRes['code'] != 200) {
                    DB::rollBack();
                    throw new Exception($payRes['msg']);
                }
            }
            # 查询用户是否曾经拥有该订阅
            $userSub = Subscribe::query()->where(['user_id' => $userId, 'goods_id' => $order['goods_id']])->first();
            # 查询购买的商品
            $goods = Goods::query()->where(['id' => $order['goods_id']])->first();
            if (!$userSub) {
                # 新增订阅
                $sub = Subscribe::query()->create([
                    'user_id'     => $userId,
                    'goods_id'    => $goods['class_id'],
                    'token_total' => $order['token_num'],
                    'end_date'    => $goods['valid_day'] == 0 ? '' : Carbon::now()->addDay($goods['valid_day']),
                    'token'       => $order['token_num'],
                ]);
                if (!$sub) {
                    DB::rollBack();
                    throw new Exception("新增订阅失败");
                }
            } else {
                # 修改订阅
                $sub = Subscribe::query()->where(['user_id' => $userId, 'goods_id' => $order['goods_id']])->update([
                    'user_id'     => $userId,
                    'goods_id'    => $goods['class_id'],
                    'token_total' => $order['token_num'] + $userSub['token_total'],
                    'end_date'    => $goods['valid_day'] == 0 ? '' : ($userSub['end_date'] > Carbon::now()->addDay($goods['valid_day']) ? $userSub['end_date'] : Carbon::now()->addDay($goods['valid_day'])),
                    'token'       => $order['token_num'] + $userSub['token'],
                ]);
                if (!$sub) {
                    DB::rollBack();
                    throw new Exception("修改订阅失败");
                }
            }
            # 走分红
            $this->commission($userId, $order);
            DB::commit();
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * User: Yan
     * DateTime: 2023/5/22
     * @return bool
     * 走分红分佣
     * @throws Exception
     */
    public function commission($userId, $order): bool
    {
        # 查询用户是否存在上级
        $toUser = Users::query()->where(['id' => $userId])->value('p_id');
        if ($toUser <= 0) {
            return true;
        }
        # 获取分佣百分比
        $percent = Config::query()->where(['group' => 'invitation', 'key' => 'commission'])->value('value');
        if ($percent <= 0) {
            return true;
        }
        # 计算奖励 金额 * 分佣百分比 * 0.1
        $money = bcmul(bcmul($order['total_amount'], $percent, 2), 10);
        # 走资金表
        try {
            $payRes = PayCommonService::userAccount($toUser, $money, '购买商品分佣', 5, $userId);
            if ($payRes['code'] != 200) {
                # 分佣失败了！
                \Log::error("分佣失败了！" . $payRes['msg']);
                throw new Exception($payRes['msg']);
            }
        } catch (ApiException $e) {
            # 分佣失败了！
            \Log::error("分佣失败了！" . $e->getMessage());
            throw new Exception($e->getMessage());
        }
        return true;
    }

    /**
     * User: Yan
     * DateTime: 2023/5/11
     * 发起订单
     * @throws Exception
     */
    public function createOrder($params): array
    {
        try {
            $user = auth('api')->user();
            # 查询商品信息
            $goods = Goods::query()->where(['id' => $params['goods_id']])->first();
            if (!$goods) {
                throw new Exception("商品不存在");
            }
            # 限购
            if ($goods['quota'] < $params['pay_num']) {
                throw new Exception("超出限购数量");
            }
            # 判断抵扣豆是否足够
            $userDiscountTotal = UsersAccount::query()->where(['user_id' => $user['id']])->orderBy('id', 'desc')->value('total_balance');
            if ($params['discount_num'] > 0) {
                if ($userDiscountTotal < $params['discount_num']) {
                    throw new Exception("享学豆不足");
                }
            }
            #抵扣豆不允许大于商品总金额
            if ($params['discount_num'] > $goods['amount']*10) {
                throw new Exception("抵扣豆不允许大于商品总金额");
            }
            # 计算购买总金额
            $totalAmount = bcmul($goods['amount'], $params['pay_num'], 2);
            # 换算抵扣豆 1个抵扣豆 等于 0.1元
            $discountAmount = bcmul($params['discount_num'], 0.1, 2);
            # 计算实际支付金额
            $amount = bcsub($totalAmount, $discountAmount, 2);

            $arr['order_no'] = $this->build_order_no();
            $arr['user_id'] = $user['id'];
            $arr['discount'] = $params['discount_num'] ?? 0;//使用抵扣豆
            $arr['amount'] = $amount;//实际支付
            $arr['total_amount'] = $totalAmount;//总金额
            $arr['pay_status'] = 1;//待支付
            $arr['goods_id'] = $params['goods_id'];
            $arr['token_num'] = bcmul($goods['token_num'], $params['pay_num'], 0);
            $arr['pay_type'] = $params['pay_type'];
            $arr['pay_num'] = $params['pay_num'];
            $arr['remark'] = $params['remark'] ?? '';
            $res = PayOrder::query()->create($arr);
            if (!$res) {
                throw new Exception("订单创建失败");
            }
            # 走资金表
            try {
                $payRes = PayCommonService::userAccount($user['id'], ($params['discount_num']*-1), '购买商品', 2, $user['id']);
                if ($payRes['code'] != 200) {
                    # 购买失败！
                    throw new Exception($payRes['msg']);
                }
            } catch (ApiException $e) {
                # 购买失败！
                \Log::error("购买失败！" . $e->getMessage());
                throw new Exception($e->getMessage());
            }
            # 拉起支付
            $resPay = false;
            switch ($params['pay_type']) {
                case 1://支付宝
                    $payModel = new Alipay();
                    $resPay = $payModel->wap_pay(['out_trade_no' => $arr['order_no'], 'total_amount' => $arr['amount'], 'subject' => $goods['title']]);
                    break;
                case 2://微信
                    $payModel = new WeChatPay();
                    $resPay = $payModel->wap_pay(['out_trade_no' => $arr['order_no'], 'total_amount' => $arr['amount'], 'subject' => $goods['title']], request()->ip());
                // todo
            }
            return ['pay_url' => $resPay, 'order_no' => $arr['order_no']];
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * User: Yan
     * DateTime: 2023/5/11
     * @return string
     * 生成不重复的订单号16位
     */
    public function build_order_no(): string
    {
        $order_no = 'SYB' . date('YmdHis') . rand(100000, 999999);
        if (PayOrder::query()->where('order_no', $order_no)->exists()) {
            $this->build_order_no();
        }
        return $order_no;
    }


}
