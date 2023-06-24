<?php


namespace App\Services\Admin\Base;


use App\Models\Base\AppMenu;
use Exception;
use Illuminate\Support\Facades\DB;

class AppMenuService
{

    /**
     * 所有菜单 APP
     * @return array
     * author Yan
     */
    public function listAll(): array
    {
        return AppMenu::getAll();
    }

    /**
     * 添加菜单
     * @param $params
     * @return bool
     * author Yan
     */
    public function create($params): bool
    {
        $result = AppMenu::query()->create([
            'position'        => $params['position'],
            'parent_id'       => $params['parent_id'],
            'jump_type'       => $params['jump_type'],
            'jump_ios'        => $params['jump_ios'],
            'jump_android'    => $params['jump_android'],
            'icon'            => $params['icon'],
            'menu_name'       => $params['menu_name'],
            'sort'            => $params['sort'],
            'is_hidden'       => $params['is_hidden'],
            'version_ios'     => $params['version_ios'],
            'version_android' => $params['version_android'],
            'stores_class'    => $params['stores_class'],
        ]);
        // 删除缓存
        AppMenu::delAppMenuAll();
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
        $result = AppMenu::updateById($id, [
            'position'        => $params['position'],
            'parent_id'       => $params['parent_id'],
            'jump_type'       => $params['jump_type'],
            'jump_ios'        => $params['jump_ios'],
            'jump_android'    => $params['jump_android'],
            'icon'            => $params['icon'],
            'menu_name'       => $params['menu_name'],
            'sort'            => $params['sort'],
            'is_hidden'       => $params['is_hidden'],
            'version_ios'     => $params['version_ios'],
            'version_android' => $params['version_android'],
            'stores_class'    => $params['stores_class'],
        ]);
        // 删除缓存
        AppMenu::delAppMenuAll();

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
            AppMenu::deleteById($id);
            // 删除缓存
            AppMenu::delAppMenuAll();
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
        $data = AppMenu::getAll();

        $data[] = [
            'parent_id' => 0,
            'name'      => '顶级',
            'id'        => 0
        ];

        return $data;
    }
}
