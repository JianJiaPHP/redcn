<?php


namespace App\Http\Controllers\Admin\Base;


use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Services\Admin\Base\AdminRoleService;
use App\Utils\Result;
use Exception;
use Illuminate\Http\JsonResponse;

class RoleController extends Controller
{
    private $adminRoleService;

    /**
     * RoleController constructor.
     */
    public function __construct(AdminRoleService $adminRoleService)
    {
        $this->adminRoleService = $adminRoleService;
    }


    /**
     * 角色列表
     * @return JsonResponse
     * @author Aii
     */
    public function index(): JsonResponse
    {
        $params = request()->all();
        $keyword = $params['name'];
        $limit = $params['limit'];
        $data = $this->adminRoleService->list($keyword, $limit);
        return Result::success($data);
    }

    /**
     * 角色添加
     * @return JsonResponse
     * @throws Exception
     * @author Aii
     */
    public function store(): JsonResponse
    {
        $params = request()->all();
        $result = $this->adminRoleService->create($params);
        return Result::choose($result);
    }


    /**
     * 角色更新
     * @param $id
     * @return JsonResponse
     * @throws Exception
     * @author Aii
     */
    public function update($id): JsonResponse
    {
        $params = request()->all();
        $result = $this->adminRoleService->update($id, $params);
        return Result::choose($result);
    }

    /**
     * 角色删除
     * @param $id
     * @return JsonResponse
     * @throws ApiException
     * @author Aii
     */
    public function destroy($id): JsonResponse
    {
        # 不删除id为 2 3的
        if (in_array($id, [2, 3])) {
            return Result::fail('不允许删除');
        }
        $result = $this->adminRoleService->delete($id);

        return Result::choose($result);
    }

    /**
     * 获取所有角色
     * @return JsonResponse
     * @author Aii
     */
    public function getAll(): JsonResponse
    {
        $data = $this->adminRoleService->all();
        return Result::success($data);
    }

}
