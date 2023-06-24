<?php


namespace App\Services\Admin\Withdrawal;


use App\Exceptions\ApiException;
use App\Models\Base\Config;
use App\Models\Withdrawal\Withdrawal;
use App\Services\Admin\Pay\PayCommonService;
use App\Utils\Alipay;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Log;
use Yansongda\Pay\Pay;

class WithdrawalService
{
    /**
     * 列表
     * @param $data
     * @return LengthAwarePaginator
     */
    public function list($data): LengthAwarePaginator
    {
        #获取提现列表数据
        $where         = [];
        $whereUserInfo = [];
        if (isset($data['withdrawal_no']) and $data['withdrawal_no']) {
            $where['withdrawal_no'] = $data['withdrawal_no'];
        }

        if (isset($data['user_id']) and $data['user_id']) {
            $where['user_id'] = $data['user_id'];
        }
        if (isset($data['card_id']) and $data['card_id']) {
            $where['card_id'] = $data['card_id'];
        }
        if (isset($data['real_name']) and $data['real_name']) {
            $where['real_name'] = $data['real_name'];
        }
        if (isset($data['zhifubao_pay']) and $data['zhifubao_pay']) {
            $where['zhifubao_pay'] = $data['zhifubao_pay'];
        }
        if (isset($data['withdrawal_type']) and $data['withdrawal_type']) {
            $where['withdrawal_type'] = $data['withdrawal_type'];
        }
        if (isset($data['status']) and $data['status']) {
            $where['status'] = $data['status'];
        }
        if (isset($data['is_appropriation']) and $data['is_appropriation']) {
            $where['is_appropriation'] = $data['is_appropriation'];
        }
        if (isset($data['username']) and $data['is_appropriation']) {
            $where['is_appropriation'] = $data['is_appropriation'];
        }

        if (isset($data['created_at'][0]) && isset($data['created_at'][1])) {
            $where[] = ['created_at', '>=', $data['created_at'][0]];
            $where[] = ['created_at', '<=', $data['created_at'][1]];
        }


        $nickname = $data['nickName'] ?? '';
        $data     = Withdrawal::query()->with('getUserInfo');
        if (!empty($nickname)) {
            $data->whereHas('getUserInfo', function ($query) use ($nickname) {
                return $query->where('nickname', 'like', '%' . $nickname . '%');
            });
        }
        $data = $data->where($where)->orderBy('id', 'desc');
        return $data->paginate(request()->query('limit', 15));

    }


    /**
     * 获取弹窗数据
     * @param $withdrawal_id
     * @return array
     */
    public function getSuccessWindowsInfo($withdrawal_id): array
    {
        #获取该条记录其他信息
        $get_withdrawal_record = Withdrawal::query()->where(['id' => $withdrawal_id, 'is_appropriation' => 1])->first();
        if (empty($get_withdrawal_record)) {
            return [];
        }
        return $get_withdrawal_record->toArray();
    }


    /**
     * 获取支付列表
     * @return array
     */
    public function getPayList(): array
    {
        #获取支付列表
//        $get_pay_list = [['id'=>1,'name'=>'支付宝'],['id'=>3,'name'=>'银行卡']];
        $get_pay_list = [['id' => 3, 'name' => '银行卡']];
        return !empty($get_pay_list) ? $get_pay_list : [];
    }


    public function changeWithdrawalStatus($id): array
    {

        $apply = Withdrawal::query()->where('id', $id)->first()->toArray();

        try {
            $this->alipayTransfer($apply['withdrawal_no'], $apply['withdrawal_price'], $apply['zhifubao_pay'], $apply['real_name']);

            \DB::beginTransaction();
            Withdrawal::query()->where('id', $id)->update([
                'is_appropriation' => 2,
                'status'           => 1
            ]);
            $payComm = new PayCommonService();
            $payComm::userAccount(
                $apply['user_id'],
                $apply['withdrawal_price'],
                '提现成功',
                2,
            );

            \DB::commit();
        } catch (Exception|ApiException $e) {
            Log::info('提现转账异常');
            \DB::rollBack();
            return ['code' => 500, 'msg' => $e->getMessage()];
        }
        return ['code' => 200, 'msg' => 'ok'];
    }

    /**
     * 变更转售订单状态
     * @param $data
     * @return array
     * @throws Exception
     */
//    public function changeWithdrawalStatus($data): array
//    {
//        $withdrawal_id = !empty($data['id']) ? $data['id'] : 0;
//        $withdrawal_status = !empty($data['status']) ? $data['status'] : 0;
//        $billing_method = !empty($data['billing_method']) ? $data['billing_method'] : 0;
//        $date_time = date('Y-m-d H:i:s', time());
//        $update_data = array('status' => $withdrawal_status, 'is_appropriation' => 2, 'updated_at' => $date_time);
//        try {
//            DB::beginTransaction();
//            $withdrawal_info = Withdrawal::query()->where(array('id' => $withdrawal_id))->first();
//            $withdrawalStatus = Withdrawal::query()->where(['id' => $withdrawal_id, 'status' => 0])->value('status');
//            if (empty($withdrawal_info)) {
//                DB::rollBack();
//                return ['code' => 500, 'msg' => "记录不存在"];
//            }
//            $withdrawal_info = $withdrawal_info->toArray();
//            if (!$withdrawalStatus) {
//                DB::rollBack();
//                return ['code' => 500, 'msg' => "该记录已处理"];
//            }
//            if ($withdrawal_status == 1) {
//                $check_balance = UsersInfo::query()->where([
//                    ['user_id', '=', $withdrawal_info['user_id']],
//                    ['frozen_balance', '>=', $withdrawal_info['actual_amount']]
//                ])->first();
//                if (empty($check_balance)) {
//                    DB::rollBack();
//                    return ['code' => 500, 'msg' => "可提现余额不足"];
//                }
//                $send_data = [
//                    'out_trade_no'    => $withdrawal_info['withdrawal_no'],
//                    'trans_amount'    => $withdrawal_info['actual_amount'],
//                    'order_title'     => '顶牛-提现',
//                    'ali_pay_account' => $withdrawal_info['zhifubao_pay'],
//                    'real_name'       => $withdrawal_info['real_name']
//                ];
//
//                if ($billing_method == 1) {
//                    $createPay = [
//                        'order_no'     => $send_data['out_trade_no'],//统一下单订单号
//                        'total_amount' => $send_data['trans_amount'],//支付金额
//                        'meal_id'      => 0,//套餐id
//                        'meal_type'    => 3,//支付业务类型
//                        'pay_type'     => 1,//支付类型 支付宝：1
//                        'store_id'     => auth('api')->id(),//支付类型 支付宝：1
//                    ];
//                    (new Alipay())->alipay_transfer($send_data);
//                    $createPay['json'] = \GuzzleHttp\json_encode($createPay);
//                    PayOrder::query()->create($createPay);
//                }
//
//                $update_data['billing_method'] = $billing_method;
//                $change_status = Withdrawal::query()->where(array('id' => $withdrawal_id, 'status' => 0))->update($update_data);
//                if (empty($change_status)) {
//                    DB::rollBack();
//                    return ['code' => 200, 'msg' => "操作失败,转出信息更新失败"];
//                }
//                # 扣除冻结金额
//                UsersInfo::query()->where('user_id', $withdrawal_info['user_id'])->decrement('frozen_balance', $withdrawal_info['withdrawal_price']);
//                Message::sendMessage('提现成功', '提现成功【' . $send_data['trans_amount'] . '】元', $withdrawal_info['user_id']);
//                DB::commit();
//                return ['code' => 200, 'msg' => "操作成功"];
//            } elseif ($withdrawal_status == '-1') {
//                ## 驳回
//                Message::sendMessage('提现失败', '您的提现有误，请核对认证信息和提现信息是否一致。如有问题请联系官方客服。', $withdrawal_info['user_id']);
//                Withdrawal::query()->where(['id' => $withdrawal_id])->update(['status' => -1]);
//                ## 资金退回
//                $userAccount = PayCommonService::UserWithAddDetailed($withdrawal_id, '提现驳回-资金返回');
//                if ($userAccount['code'] != 200) {
//                    DB::rollBack();
//                    return ['code' => 500, 'msg' => $userAccount['msg']];
//                }
//                DB::commit();
//            }
//        } catch (Exception $e) {
//            DB::rollBack();
//            if ($e->getMessage() == "ERROR_GATEWAY: Get Alipay API Error:Business Failed - PAYEE_NOT_EXIST") {
//                return ['code' => 500, 'msg' => "收款人不存在或收款人姓名与账户不匹配！"];
//            }
//            Log::error('提现失败', ['msg' => $e->getMessage()]);
//            throw new Exception("系统错误,转出失败");
//        }
//        return ['code' => 200, 'msg' => "操作成功"];
//
//    }


    /**
     * User: Yan
     * DateTime: 2023/3/3
     * @return array 获取提现配置
     * 获取提现配置
     */
    public function getWithdrawalConfig(): array
    {
        return Config::getByWhere(['withdrawal']) ?? [
            [
                "key"   => "min",
                "value" => "100",
                "desc"  => "提现最低提现金额",
                "group" => "withdrawal"
            ],
            [
                "key"   => "max",
                "value" => "10000",
                "desc"  => "提现最高提现金额",
                "group" => "withdrawal"
            ],
            [
                "key"   => "fee",
                "value" => "0.01",
                "desc"  => "提现手续费",
                "group" => "withdrawal"
            ],
            [
                "key"   => "content",
                "value" => "提现会在1-3个工作日到账",
                "desc"  => "提现说明文本",
                "group" => "withdrawal"
            ]

        ];
    }

    /**
     * User: Yan
     * DateTime: 2023/3/3
     * @param $data
     * @return bool 修改提现配置
     * 修改提现配置
     */
    public function updateWithdrawalConfig($data): bool
    {
        $config = new Config();
        foreach ($data as $value) {
            $config->updateOrCreate([
                "key"   => $value['key'],
                "value" => $value['value'],
                "desc"  => $value['desc'],
                "group" => $value['group']
            ], $value['id'] ?? null);
        }
        return true;
    }


    /**
     * @param string $order
     * @param float $money
     * @param $identity
     * @param $name
     * @return void
     * @throws Exception
     * @Time 2023/5/19 22:06
     * @author sunsgne
     */
    protected function alipayTransfer(string $order, float $money, $identity, $name)
    {
        $a = new Alipay();

        $name     = config('app.debug') ? 'wkslug3362' : $name;
        $identity = config('app.debug') ? 'wkslug3362@sandbox.com' : $identity;

        $result = Pay::alipay(config('pay'))->transfer([
            'out_biz_no'   => $order,
            'trans_amount' => $money,
            'product_code' => 'TRANS_ACCOUNT_NO_PWD',
            'biz_scene'    => 'DIRECT_TRANSFER',
            'payee_info'   => [
                'identity'      => $identity,
                'identity_type' => 'ALIPAY_LOGON_ID',
                'name'          => $name
            ],
        ]);
        if (!($result['code'] == 10000 && $result['msg'] === 'Success')) {
            throw new Exception($result['msg']);
        }


    }


}
