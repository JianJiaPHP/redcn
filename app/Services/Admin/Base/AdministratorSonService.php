<?php


namespace App\Services\Admin\Base;


use App\Exceptions\ApiException;
use App\Models\Base\AdministratorSon;
use App\Models\Base\AdminMenu;
use App\Models\Base\AdminResource;
use App\Models\Base\AdminRole;
use App\Models\Base\AdminRoleAdministrator;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * 管理员服务
 * Class AdministratorServiceImpl
 * @package App\Services\impl
 */
class AdministratorSonService
{
    /**
     * 列表
     * @param $account
     * @param $limit //条数
     * author Yan
     * @param $role
     * @return LengthAwarePaginator
     * @throws ApiException
     */
    public function list($account, $limit, $role): LengthAwarePaginator
    {
        try {
            return AdministratorSon::list($account, $limit, $role);
        } catch (Exception $e) {
            throw new ApiException($e->getMessage());
        }
    }

    /**
     * 添加
     * @param $params
     * author Yan
     * @throws ApiException
     */
    public function add($params, $role): bool
    {
        $exist = AdministratorSon::getByWhereOne(['account' => $params['account']]);
        if ($exist) {
            throw new ApiException("该账号已存在");
        }
        $now = Carbon::now()->toDateTimeString();
        $md5Password = md5($params['password']);
//        try {
        DB::beginTransaction();
        $create = [
            'account'  => $params['account'],
            'nickname' => $params['nickname'],
            'avatar'   => $params['avatar'] ?? 'https://placeimg.com/300/200',
            'password' => Hash::make($md5Password),
            'uuid'     => $params['uuid'] ?? ''
        ];
        if (!empty($role)) {
            if ($role['role'] == AdminRole::ROLE_STORE) {
                $create['store_id'] = auth('api')->user()['store_id'];
            } elseif ($role['role'] == AdminRole::ROLE_TUTOR) {
                $create['tutor_id'] = auth('api')->user()['tutor_id'];
            }
        }
        $result = AdministratorSon::query()->create($create);
        // 添加角色管理员
        if (!empty($role)) {
            #查询当前账号权限
            $role = AdminRoleAdministrator::query()->where(['administrator_id' => auth('api')->id()])->get()->toArray();
            $rolesData = [];
            foreach ($role as $v) {
                $rolesData[] = [
                    'role_id'          => $v['role_id'],
                    'administrator_id' => $result->id,
                    'created_at'       => $now
                ];
            }
            AdminRoleAdministrator::query()->insert($rolesData);
        }

        DB::commit();
        return true;

//        } catch (Exception $exception) {
//            DB::rollback();
//            throw new ApiException($exception->getMessage());
//        }
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
            AdministratorSon::destroy($id);

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
     * @param $id
     * @param $params
     * @return int
     * author Yan
     * @throws ApiException
     */
    public function update($id, $params): int
    {
        $user = AdministratorSon::query()->where(['id' => $id])->value('password');
        if (!$user) {
            throw new ApiException("用户不存在");
        }
        if (!Hash::check(md5($params['old_password']), $user)) {
            throw new ApiException("原密码错误");
        }
        $password = Hash::make(md5($params['password']));

        return AdministratorSon::updateById($id, [
            'password' => $password
        ]);
    }

}
