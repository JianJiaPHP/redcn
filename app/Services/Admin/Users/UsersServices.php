<?php

namespace App\Services\Admin\Users;

use App\Models\chat\Subscribe;
use App\Models\Pay\PayOrder;
use App\Models\Prompt\UserPrompt;
use App\Models\User\Users;
use App\Models\User\UsersAccount;
use App\Models\User\UsersLog;
use App\Models\User\UsersLoginLog;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use jianyan\excel\Excel;

class UsersServices
{

    /**
     * User: Yan
     * DateTime: 2023/2/24
     * @param $params
     * @return LengthAwarePaginator 用户列表
     * 用户列表
     */
    public function userList($params): LengthAwarePaginator
    {
        $where = [];
        # 昵称
        if (isset($params['nickname']) && $params['nickname']) {
            $where[] = ['nickname', 'like', '%' . $params['nickname'] . '%'];
        }
        # 手机号
        if (isset($params['phone']) && $params['phone']) {
            $where[] = ['phone', 'like', '%' . $params['phone'] . '%'];
        }
        if (isset($params['is_fellow']) && $params['is_fellow']) {
            $where[] = ['is_fellow', '=', $params['is_fellow']];
        }

        if (isset($params['p_id']) && $params['p_id']) {
            $where[] = ['p_id', '=', $params['p_id']];
        }
        # 注册来源
        if (isset($params['register_type']) && $params['register_type']) {
            $where[] = ['register_type', '=', $params['register_type']];
        }
        # 注册时间开始
        if (isset($params['start_time']) && $params['start_time']) {
            $where[] = ['register_date', '>=', $params['start_time']];
        }
        # 注册时间结束
        if (isset($params['end_time']) && $params['end_time']) {
            $where[] = ['register_date', '<=', $params['end_time']];
        }
        # 最后一次ip
        if (isset($params['last_login_ip']) && $params['last_login_ip']) {
            $where[] = ['last_login_ip', 'like', '%' . $params['last_login_ip'] . '%'];
        }
        if (isset($params['user_id']) && $params['user_id'] != '') {
            $where[] = ['id', '=', $params['user_id']];
        }
        return Users::query()->where($where)->orderBy('id', 'desc')->paginate(request()->query('limit', 15));
    }


    /**
     * 获取用户资金记录
     * @param $id
     * @return LengthAwarePaginator
     * @Time 2023/5/19 11:43
     * @author sunsgne
     */
    public function account($id): LengthAwarePaginator
    {
        return UsersAccount::query()->where('user_id', $id)->orderBy('id', 'desc')->paginate(request()->query('limit', 15));
    }


    /**
     * 获取用户模型
     * @param $id
     * @return LengthAwarePaginator
     * @Time 2023/5/19 11:46
     * @author sunsgne
     */
    public function prompt($id): LengthAwarePaginator
    {
        return UserPrompt::query()->where('user_id', $id)->orderBy('id', 'desc')->paginate(request()->query('limit', 15));
    }


    public function subscribe($id): LengthAwarePaginator
    {
        return Subscribe::query()->with('goods')->where('user_id', $id)->orderBy('id', 'desc')->paginate(request()->query('limit', 15));
    }

    /**
     * User: Yan
     * DateTime: 2023/2/24
     * @param $id
     * @param $params
     * @return bool
     * @throws Exception
     */
    public function update($id, $params): bool
    {
        $user = Users::query()->find($id);
        if (!$user) {
            throw new Exception('用户不存在');
        }
        $where = [];
        if (!empty($params['is_fellow'])) {
            $where['is_fellow'] = $params['is_fellow'];
        }
        if (!empty($params['nickname'])) {
            $where['nickname'] = $params['nickname'];
        }
        if (!empty($params['is_real_name'])) {
            $where['is_real_name'] = $params['is_real_name'];
        }
        if (!empty($params['is_withdrawal'])) {
            $where['is_withdrawal'] = $params['is_withdrawal'];
        }
        if (!empty($params['is_enabled'])) {
            $where['is_enabled'] = $params['is_enabled'];
        }
        if (!empty($params['zhifubao_pay'])) {
            $where['zhifubao_pay'] = $params['zhifubao_pay'];
        }
        if (!empty($params['p_id'])) {
            $where['p_id'] = $params['p_id'];
        }
        if (!empty($params['invitation'])) {
            $where['invitation'] = $params['invitation'];
        }
        if (!empty($params['birthday'])) {
            $where['birthday'] = $params['birthday'];
        }
        if (!empty($params['phone'])) {
            $where['phone'] = $params['phone'];
        }
        if (!empty($params['avatar'])) {
            $where['avatar'] = $params['avatar'];
        }
        if (!empty($params['sex'])) {
            $where['sex'] = $params['sex'];
        }


        if (!empty($params['password'])) {
            $where['password'] = \Hash::make(md5($params['password']));
        }
        return Users::query()->where('id', $id)->update($where);
    }

    /**
     * 根据ID获取下级用户
     * @param int $id
     * @return LengthAwarePaginator
     * @Time 2023/5/24 17:38
     * @author sunsgne
     */
    public function GetSubsetUserList(int  $id): LengthAwarePaginator
    {
        return Users::query()->where(['p_id' => $id])->orderBy('id', 'desc')->paginate(request()->query('limit', 15));
    }

    /**
     * 赠送订阅给用户
     * @param int $userId
     * @param array $params
     * @return bool
     * @throws Exception
     * @Time 2023/5/22 14:00
     * @author sunsgne
     */
    public function giveAwaySubscribe(int $userId, array $params): bool
    {

        try {
            DB::beginTransaction();
            Subscribe::query()->insert([
                'user_id'     => $userId,
                'goods_id'    => $params['goods_id'],
                'token_total' => $params['token_total'],
                'token'       => $params['token'],
                'end_date'    => $params['end_date'],
                'created_at'  => $time = Carbon::now()->toDateTimeString(),
                'updated_at'  => $time
            ]);

            PayOrder::query()->insert([
                'order_no'     => $this->build_order_no(),
                'user_id'      => $userId,
                'total_amount' => 0,
                'pay_status'   => 5,
                'goods_id'     => $params['goods_id'],
                'token_num'    => $params['token_total'],
                'pay_type'     => 3,
                'remark'       => '赠送订阅',
                'created_at'   => $time,
                'updated_at'   => $time
            ]);
            DB::commit();
            return true;
        } catch (Exception $exception) {
            Log::error($exception->getMessage());
            DB::rollBack();
            return false;
        }

    }

    public function build_order_no(): string
    {
        $order_no = 'SYB' . date('YmdHis') . rand(100000, 999999);
        if (PayOrder::query()->where('order_no', $order_no)->exists()) {
            $this->build_order_no();
        }
        return $order_no;
    }

    /**
     * User: Yan
     * DateTime: 2023/2/24
     * @param $id
     * @return LengthAwarePaginator
     * 用户登录日志
     */
    public function loginLog($id): LengthAwarePaginator
    {
        return UsersLoginLog::query()->where('user_id', $id)->orderBy('id', 'desc')->paginate(request()->query('limit', 15));
    }

    /**
     * User: Yan
     * DateTime: 2023/2/24
     * @param $id
     * @return LengthAwarePaginator 用户行为日志
     * 用户行为日志
     */
    public function usersLog($id): LengthAwarePaginator
    {
        return UsersLog::query()->where('user_id', $id)->orderBy('id', 'desc')->paginate(request()->query('limit', 15));
    }

    /**
     * User: Yan
     * DateTime: 2023/4/4
     * @return bool 用户导出
     * 用户导出
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     * @throws Exception
     */
    public function export($params): bool
    {
        $where = [];
        # 昵称
        if (isset($params['nickname']) && $params['nickname']) {
            $where[] = ['nickname', 'like', '%' . $params['nickname'] . '%'];
        }
        # 手机号
        if (isset($params['phone']) && $params['phone']) {
            $where[] = ['phone', 'like', '%' . $params['phone'] . '%'];
        }
        # 注册来源
        if (isset($params['register_type']) && $params['register_type']) {
            $where[] = ['register_type', '=', $params['register_type']];
        }
        # 注册时间开始
        if (isset($params['start_time']) && $params['start_time']) {
            $where[] = ['registration_time', '>=', $params['start_time']];
        }
        # 注册时间结束
        if (isset($params['end_time']) && $params['end_time']) {
            $where[] = ['registration_time', '<=', $params['end_time']];
        }
        # 最后一次ip
        if (isset($params['last_ip']) && $params['last_ip']) {
            $where[] = ['last_ip', 'like', '%' . $params['last_ip'] . '%'];
        }
        if (isset($params['user_id']) && $params['user_id'] != '') {
            $where[] = ['id', '=', $params['user_id']];
        }
        $list = Users::query()->with(['userInfo'])->where($where)->orderBy('id', 'desc')->get();
        if ($list->isEmpty()) {
            throw new Exception('暂无数据');
        }
        $list = $list->toArray();
        $data = [];
        foreach ($list as &$v) {
            $data[] = [
                'id'                       => $v['id'],
                'nickname'                 => $v['nickname'],//昵称
                'last_login_time'          => Carbon::parse($v['last_login_time'])->toDateTimeString(),//最后一次登录时间
                'registration_time'        => Carbon::parse($v['registration_time'])->toDateTimeString(),//注册时间
                'last_ip'                  => $v['last_ip'],//最后一次登录ip
                'created_at'               => Carbon::parse($v['created_at'])->toDateTimeString(),//账户创建时间
                'phone'                    => $v['phone'],//手机号
                'register_type'            => $v['register_type'],//注册类型: 1:抖音授权注册  2:手机号注册
                'is_withdrawal'            => $v['is_withdrawal'],//是否禁止提现1:禁止 0:可以体现
                'is_ban'                   => $v['is_ban'],//1:封禁  0：正常
                'user_info_is_real_name'   => $v['user_info']['is_real_name'] ?? 0,//是否实名1：实名 0：未实名
                'user_info_real_name'      => $v['user_info']['real_name'] ?? '',//真实姓名
                'user_info_id_card'        => $v['user_info']['is_real_name'] ?? '',//身份证号
                'user_info_money'          => $v['user_info']['is_real_name'] ?? 0,//账户余额
                'user_info_is_store'       => $v['user_info']['is_store'] ?? 0,//是否是商家1：是 0：不是
                'user_info_is_tutor'       => $v['user_info']['is_tutor'] ?? 0,//是否是导师1：是 0：不是
                'user_info_frozen_balance' => $v['user_info']['frozen_balance'] ?? 0,//冻结金额
            ];
        }
        $header = [
            ['ID', 'id'],
            ['昵称', 'nickname'],
            ['最后一次登录时间', 'last_login_time'],
            ['注册时间', 'registration_time'],
            ['最后一次登录ip', 'last_ip'],
            ['账户创建时间', 'created_at'],
            ['手机号', 'phone'],
            ['注册类型', '注册类型', 'function', function ($model) {
                $register_type = [
                    1 => '抖音授权注册',
                    2 => '手机号注册',
                ];
                return $register_type[$model['register_type']];
            }],
            ['是否禁止提现', '是否禁止提现', 'function', function ($model) {
                $is_withdrawal = [
                    1 => '禁止',
                    0 => '可以提现',
                ];
                return $is_withdrawal[$model['is_withdrawal']];
            }],
            ['是否被封禁', '是否被封禁', 'function', function ($model) {
                $is_ban = [
                    1 => '封禁',
                    0 => '正常',
                ];
                return $is_ban[$model['is_ban']];
            }],
            ['是否实名', '是否实名', 'function', function ($model) {
                $user_info_is_real_name = [
                    1 => '实名',
                    0 => '未实名',
                ];
                return $user_info_is_real_name[$model['user_info_is_real_name']];
            }],
            ['真实姓名', 'user_info_real_name'],
            ['身份证号', 'user_info_id_card'],
            ['账户余额', 'user_info_money'],
            ['是否是商家', '是否是商家', 'function', function ($model) {
                $user_info_is_store = [
                    1 => '是',
                    0 => '不是',
                ];
                return $user_info_is_store[$model['user_info_is_store']];
            }],
            ['是否是导师', '是否是导师', 'function', function ($model) {
                $user_info_is_tutor = [
                    1 => '是',
                    0 => '不是',
                ];
                return $user_info_is_tutor[$model['user_info_is_tutor']];
            }],
            ['冻结金额', 'user_info_frozen_balance'],
        ];
        return Excel::exportData($data, $header, '用户列表列表数据' . Carbon::now(), 'xlsx');
    }
}
