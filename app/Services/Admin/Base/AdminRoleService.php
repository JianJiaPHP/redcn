<?php


namespace App\Services\Admin\Base;


use App\Exceptions\ApiException;
use App\Models\Base\AdminMenu;
use App\Models\Base\AdminResource;
use App\Models\Base\AdminRole;
use App\Models\Base\AdminRoleAdministrator;
use App\Models\Base\AdminRoleMenu;
use App\Models\Base\AdminRoleResource;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Log;

class AdminRoleService
{

    /**
     * 列表
     * @param $keyword
     * @param $limit
     * @return LengthAwarePaginator
     * author Yan
     */
    public function list($keyword, $limit): LengthAwarePaginator
    {
        return AdminRole::list($keyword, $limit);
    }

    /**
     * 添加
     * @param $params
     * @return bool
     * author Yan
     * @throws Exception
     */
    public function create($params)
    {
        $now = Carbon::now()->toDateTimeString();
        DB::beginTransaction();
        try {
            $role = AdminRole::query()->create([
                'name' => $params['name'],
                'desc' => $params['description'],
            ]);

            if (!empty($params['menus'])) {
                $menuData = [];
                $menus = explode(',', $params['menus']);
                $menus = array_values(array_unique($menus));
                foreach ($menus as $v) {
                    $menuData[] = [
                        'menu_id'    => $v,
                        'role_id'    => $role->id,
                        'created_at' => $now
                    ];
                }
                AdminRoleMenu::query()->insert($menuData);

            }

            if (!empty($params['resources'])) {
                $resourcesData = [];
                $resources = explode(',', $params['resources']);
                $resources = array_values(array_unique($resources));
                foreach ($resources as $v) {
                    $resourcesData[] = [
                        'resource_id' => $v,
                        'role_id'     => $role->id,
                        'created_at'  => $now
                    ];
                }
                AdminRoleResource::query()->insert($resourcesData);
            }

            DB::commit();
            return true;

        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            DB::rollback();
            return false;
        }
    }

    /**
     * 更新
     * @param $id
     * @param $params
     * @return bool
     * author Yan
     * @throws Exception
     */
    public function update($id, $params)
    {
        $now = Carbon::now()->toDateTimeString();
        DB::beginTransaction();
        try {
            AdminRole::query()->where('id', $id)->update([
                'name' => $params['name'],
                'desc' => $params['description'],
            ]);

            // 删除角色资源
            AdminRoleResource::deleteByWhere(['role_id' => $id]);

            // 删除角色菜单
            AdminRoleMenu::deleteByWhere(['role_id' => $id]);

            // 添加角色菜单
            if (!empty($params['menus'])) {
                $menuData = [];
                $menus = explode(',', $params['menus']);
                $menus = array_values(array_unique($menus));
                foreach ($menus as $v) {
                    $menuData[] = [
                        'menu_id'    => $v,
                        'role_id'    => $id,
                        'created_at' => $now
                    ];
                }
                AdminRoleMenu::query()->insert($menuData);

            }

            // 添加角色资源
            if (!empty($params['resources'])) {
                $resourcesData = [];
                $resources = explode(',', $params['resources']);
                $resources = array_values(array_unique($resources));
                foreach ($resources as $v) {
                    $resourcesData[] = [
                        'resource_id' => $v,
                        'role_id'     => $id,
                        'created_at'  => $now
                    ];
                }
                AdminRoleResource::query()->insert($resourcesData);

            }
            // 和此角色有关的管理员ID
            $administratorIds = AdminRoleAdministrator::getAdministratorIdByRoleId($id);
            foreach ($administratorIds as $v) {
                // 删除资源缓存
                AdminResource::delAdminResourceByAdministratorId($v);
                // 删除菜单缓存
                AdminMenu::delAdminByAdministratorId($v);
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
     * author Yan
     * @throws ApiException
     */
    public function delete($id)
    {
        $exist = AdminRoleAdministrator::getOneByWhere(['role_id' => $id]);
        if ($exist) {
            throw new ApiException("该角色下还有管理员不能删除");
        }

        DB::beginTransaction();
        try {

            AdminRole::destroy($id);

            AdminRoleResource::deleteByWhere(['role_id' => $id]);

            AdminRoleMenu::deleteByWhere(['role_id' => $id]);

            // 和此角色有关的管理员ID
            $administratorIds = AdminRoleAdministrator::getAdministratorIdByRoleId($id);
            foreach ($administratorIds as $v) {
                // 删除资源缓存
                AdminResource::delAdminResourceByAdministratorId($v);
                // 删除菜单缓存
                AdminMenu::delAdminByAdministratorId($v);
            }

            DB::commit();
            return true;

        } catch (Exception $exception) {
            DB::rollback();
            return false;
        }
    }

    /**
     * 所有角色
     * @return Builder[]|Collection
     * author Yan
     */
    public function all()
    {
        return AdminRole::getAll();
    }
}
