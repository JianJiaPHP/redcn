<?php


namespace App\Services\Admin\Base;


use App\Exceptions\ApiException;
use App\Models\Base\Administrator;
use App\Models\Base\AdminLoginLog;
use App\Models\Base\AdminMenu;
use App\Models\Base\AdminResource;
use App\Models\Base\AdminRoleAdministrator;
use App\Models\chat\Subscribe;
use App\Models\Pay\PayOrder;
use App\Models\User\Users;
use App\Models\Withdrawal\Withdrawal;
use App\Utils\Ip;
use App\Utils\Result;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Yansongda\Pay\Pay;

/**
 * 管理员服务
 * Class AdministratorServiceImpl
 * @package App\Services\impl
 */
class AdministratorService
{


    /**
     * User: Yan
     * DateTime: 2023/3/7
     * @param $id
     * @return array 首页统计
     * 首页统计
     */
    public function homeData($id):array
    {
        $data = [];
        # 用户统计
        $data['user']['user_count'] = Users::query()->count();
        # 订单统计
        $data['order']['order_count'] = PayOrder::query()->count();
        # 订单金额统计
        $data['order']['order_amount'] = PayOrder::query()->sum('amount');
        # 订单支付统计
        $data['order']['order_pay_count'] = PayOrder::query()->where('pay_status', 3)->count();
        # 订单支付金额统计
        $data['order']['order_pay_amount'] = PayOrder::query()->where('pay_status', 3)->sum('amount');
        # 今日订单统计
        $data['order']['order_today_count'] = PayOrder::query()->whereDate('created_at', Carbon::today())->count();
        # 今日订单金额统计
        $data['order']['order_today_amount'] = PayOrder::query()->whereDate('created_at', Carbon::today())->sum('amount');

        # 订阅统计
        $data['subscribe']['subscribe_count'] = Subscribe::query()->where('end_date','>=',Carbon::today())->count();
        # 今日订阅统计
        $data['subscribe']['subscribe_today_count'] = Subscribe::query()->whereDate('created_at', Carbon::today())->count();
        # 剩余订阅Token
        $data['subscribe']['subscribe_token'] = Subscribe::query()->where('end_date','>=',Carbon::today())->sum('token');

        # 提现金额统计
        $data['withdraw']['withdraw_amount'] = Withdrawal::query()->where('status', 1)->sum('actual_amount');
        # 待审核提现统计
        $data['withdraw']['withdraw_wait_count'] = Withdrawal::query()->where('status', 0)->count();

        return $data;
    }


    /**
     * 列表
     * @param $account
     * @param $limit //条数
     * author Yan
     * @return LengthAwarePaginator
     */
    public function list($account, $limit): LengthAwarePaginator
    {
        return Administrator::list($account, $limit);
    }

    /**
     * 添加
     * @param $params
     * author Yan
     * @throws ApiException
     */
    public function add($params): bool
    {
        $exist = Administrator::getByWhereOne(['account' => $params['account']]);
        if ($exist) {
            throw new ApiException("该账号已存在");
        }
        $now = Carbon::now()->toDateTimeString();
        # 判断密码不能少于6位
        if (strlen($params['password']) < 6) {
            throw new ApiException("密码不能少于6位");
        }
        $md5Password = md5($params['password']);
        DB::beginTransaction();
        try {
            $result = Administrator::query()->create([
                'account'  => $params['account'],
                'nickname' => $params['nickname'],
                'avatar'   => $params['avatar'] ?? 'https://placeimg.com/300/200',
                'password' => Hash::make($md5Password),
                'uuid'     => $params['uuid'] ?? ''
            ]);
            // 添加角色管理员
            if (!empty($params['roleIds'])) {
                $rolesData = [];
                $roles = explode(',', $params['roleIds']);
                foreach ($roles as $v) {
                    $rolesData[] = [
                        'role_id'          => $v,
                        'administrator_id' => $result->id,
                        'created_at'       => $now
                    ];
                }
                AdminRoleAdministrator::query()->insert($rolesData);
            }

            DB::commit();
            return true;

        } catch (Exception $exception) {
            DB::rollback();
            return false;
        }
    }

    /**
     * 删除
     * @param $id
     * @return bool
     * @throws Exception
     * author Yan
     */
    public function destroy($id): bool
    {
        DB::beginTransaction();
        try {
            Administrator::destroy($id);

            AdminRoleAdministrator::deleteByWhere(['administrator_id' => $id]);
            // 删除菜单缓存
            AdminMenu::delAdminByAdministratorId($id);
            // 删除资源缓存
            AdminResource::delAdminResourceByAdministratorId($id);

            DB::commit();
            return true;

        } catch (Exception $exception) {
            DB::rollback();
            return false;
        }
    }

    /**
     * 修改密码
     * @param $params
     * @return int
     * author Yan
     * @throws ApiException
     */
    public function updatePwd($params): int
    {
        $user = auth('api')->user();

        if (!Hash::check(md5($params['old_password']), $user['password'])) {
            throw new ApiException("原密码错误");
        }
        $password = Hash::make(md5($params['password']));

        return Administrator::updateById($user['id'], [
            'password' => $password
        ]);
    }

    /**
     * 修改uuid
     * @param $params
     * @return int
     * author Yan
     */
    public function updateUuid($params): int
    {
        $user = auth('api')->user();

        return Administrator::updateById($user['id'], [
            'uuid' => $params['uuid_confirmation'] ?? ''
        ]);

    }

    /**
     * 更新个人信息
     * @param $params
     * @return int
     * author Yan
     * @throws ApiException
     */
    public function updateInfo($params): int
    {
        $user = auth('api')->user();

        $oldData = Administrator::getByWhereOne(['id' => $user['id']]);
        if (!$oldData) {
            throw new ApiException();
        }

        if ($oldData->account != $params['account']) {
            $exist = Administrator::getByWhereOne(['account' => $params['account']]);
            if ($exist) {
                throw new ApiException("该账号已存在");
            }
        }
        return Administrator::updateById($user['id'], [
            'nickname' => $params['nickname'],
            'account'  => $params['account'],
        ]);
    }

    /**
     * 获取个人信息
     * @return array
     * author Yan
     */
    public function getInfo(): array
    {
        $id = auth('admin')->id();
        $adminInfo = Administrator::getAdministratorById($id);
        # 获取角色名称
        $roles = AdminRoleAdministrator::getRoleIdsByAdministratorId($id);
        $adminInfo['roles'] = $roles;
        return $adminInfo;
    }

    /**
     * 登录
     * @param $account
     * @param $password
     * @param $secret
     * @param $google_code
     * @param $ip
     * @return array
     * author Yan
     * @throws ApiException
     */
    public function login($account, $password, $ip): array
    {
        $exist = Administrator::query()->where('account', $account)->first();

        if (!$exist) {
            throw new ApiException("账号不存在");
        }

        if (!Hash::check(md5($password), $exist->password)) {
            throw new ApiException("密码错误");
        }
        $token = auth('admin')->login($exist);
        if (!$token) {
            throw new ApiException("登录失败！");
        }
        if ($ip != '127.0.0.1') {
            $result = Ip::getIpInfo($ip);
            if ($result) {
                $log = [
                    'uid'     => $exist->id,
                    'ip'      => $ip,
                    'country' => !empty($result['province']) ? $result['province'] : '',
                    'city'    => !empty($result['city']) ? $result['city'] : '',
                ];
            } else {
                $log = [
                    'uid' => $exist->id,
                    'ip'  => $ip,
                ];
            }
            AdminLoginLog::query()->create($log);
        }
        $exist->save();
        return [
            'access_token' => $token,
            'token_type'   => 'bearer',
        ];
    }

    /**
     * 更新
     * @param $id
     * @param $params
     * @return bool
     * @throws Exception
     * author Yan
     */
    public function update($id, $params): bool
    {
        $now = Carbon::now()->toDateTimeString();
        $oldData = Administrator::getByWhereOne(['id' => $id]);
        if (!$oldData) {
            throw new ApiException("不存在该用户");
        }
        if ($oldData->account != $params['account']) {
            $exist = Administrator::getByWhereOne(['account' => $params['account']]);
            if ($exist) {
                throw new ApiException("该账号已存在");
            }
        }

        $updateData = [
            'account'  => $params['account'],
            'nickname' => $params['nickname'],
            'avatar'   => $params['avatar'] ?? 'http://placeimg.com/300/200',
            'uuid'     => $params['uuid'] ?? '',
        ];
        if (!empty($params['password'])) {
            # 判断密码不能少于6位
            if (strlen($params['password']) < 6) {
                throw new ApiException("密码不能少于6位");
            }
            $md5Password = md5($params['password']);
            $updateData ['password'] = Hash::make($md5Password);
        }
        DB::beginTransaction();
        try {
            Administrator::updateById($id, $updateData);

            AdminRoleAdministrator::deleteByWhere(['administrator_id' => $id]);

            // 添加角色管理员
            if (!empty($params['roleIds'])) {
                $rolesData = [];
                $roles = explode(',', $params['roleIds']);
                foreach ($roles as $v) {
                    $rolesData[] = [
                        'role_id'          => $v,
                        'administrator_id' => $id,
                        'created_at'       => $now
                    ];
                }
                AdminRoleAdministrator::query()->insert($rolesData);

            }
            // 删除菜单缓存
            AdminMenu::delAdminByAdministratorId($id);
            // 删除资源缓存
            AdminResource::delAdminResourceByAdministratorId($id);
            DB::commit();
            return true;

        } catch (Exception $exception) {
            DB::rollback();
            return false;
        }
    }

    /**
     * User: Yan
     * DateTime: 2023/2/22
     * @return bool 退出登录
     * 退出登录
     */
    public function logout(): bool
    {
        auth('api')->logout();
        return true;
    }
}
