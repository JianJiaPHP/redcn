<?php


namespace App\Services\Admin\Base;


use App\Models\Base\AdminMenu;
use App\Models\Base\AdminRoleMenu;
use Exception;
use Illuminate\Support\Facades\DB;

class AdminMenuService
{

    /**
     * 所有菜单
     * @return array
     * author Yan
     */
    public function listAll(): array
    {
        return AdminMenu::getAll();
    }

    /**
     * 添加菜单
     * @param $params
     * @return bool
     * author Yan
     */
    public function create($params): bool
    {
        $result = AdminMenu::query()->create([
            'parent_id' => $params['parent_id'],
            'path'      => $params['path'],
            'icon'      => $params['icon'],
            'name'      => $params['name'],
            'sort'      => $params['sort'],
            'is_hidden' => $params['is_hidden'],
        ]);
        // 删除缓存
        AdminMenu::delAdminMenuAll();
        return (bool)$result;
    }

    /**
     * 更新菜单
     * @param $id
     * @param $params
     * @return bool
     * author Yan
     */
    public function update($id, $params): bool
    {
        $result = AdminMenu::updateById($id, [
            'parent_id' => $params['parent_id'],
            'path'      => $params['path'],
            'icon'      => $params['icon'],
            'name'      => $params['name'],
            'sort'      => $params['sort'],
            'is_hidden' => $params['is_hidden'],
        ]);
        // 删除缓存
        AdminMenu::delAdminMenuAll();

        return (bool)$result;
    }

    /**
     * 删除菜单
     * @param $id
     * @return bool
     * @throws Exception
     * author Yan
     */
    public function delete($id): bool
    {
        DB::beginTransaction();
        try {
            AdminMenu::deleteById($id);

            AdminRoleMenu::deleteByWhere(['menu_id' => $id]);

            // 删除缓存
            AdminMenu::delAdminMenuAll();
            DB::commit();
            return true;

        } catch (Exception $exception) {
            DB::rollback();
            return false;
        }
    }

    /**
     * 有顶级的所有菜单
     * @return array
     * author Yan
     */
    public function listTop(): array
    {
        $data = AdminMenu::getAll();

        $data[] = [
            'parent_id' => 0,
            'name'      => '顶级',
            'id'        => 0
        ];

        return $data;
    }

    /**
     * 获取管理员菜单
     * @return array
     * author Yan
     */
    public function getMenu(): array
    {
        $uid = auth('admin')->id();
        return AdminMenu::getNav($uid);
    }
}
