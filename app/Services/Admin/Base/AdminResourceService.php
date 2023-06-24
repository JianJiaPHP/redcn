<?php


namespace App\Services\Admin\Base;


use App\Models\Base\AdminResource;
use App\Models\Base\AdminRoleResource;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class AdminResourceService
{

    /**
     * 列表
     * @param $name
     * @param $url
     * @param $limit
     * @return LengthAwarePaginator
     * author Yan
     */
    public function list($name, $url, $limit): LengthAwarePaginator
    {
        return AdminResource::list($name, $url, $limit);
    }

    /**
     * 添加
     * @param $params
     * @return mixed
     * author Yan
     */
    public function create($params): void
    {
        AdminResource::query()->create([
            'name'        => $params['name'],
            'http_method' => $params['http_method'],
            'url'         => $params['url'],
        ]);
        // 删除所有的缓存
        AdminResource::delAdminResourceAll();
    }

    /**
     * 更新
     * @param $id
     * @param $params
     * @return int
     * author Yan
     */
    public function update($id, $params): int
    {
        $result = AdminResource::updateById($id, [
            'name'        => $params['name'],
            'http_method' => $params['http_method'],
            'url'         => $params['url'],
        ]);
        // 删除所有的缓存
        AdminResource::delAdminResourceAll();
        return $result;
    }

    /**
     * 删除
     * @param $id
     * @return bool
     * author Yan
     * @throws Exception
     */
    public function delete($id): bool
    {
        DB::beginTransaction();
        try {
            AdminResource::deleteById($id);
            AdminRoleResource::deleteByWhere(['resource_id' => $id]);

            // 删除所有的缓存
            AdminResource::delAdminResourceAll();
            DB::commit();
            return true;

        } catch (Exception $exception) {
            DB::rollback();
            return false;
        }
    }

    /**
     * 获取所有
     * @return array
     * author Yan
     */
    public function all(): array
    {
        return AdminResource::getAll();
    }

    /**
     * User: Yan
     * DateTime: 2023/2/22
     * @return mixed|null 根据我当前用户角色获取资源
     * 根据我当前用户角色获取资源
     */
    public function adminAll()
    {
        $admin_id = auth('api')->id();
        // 获取用户所有资源
        return AdminResource::getAdminResourceByAdministratorId($admin_id);
    }
}
