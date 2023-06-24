<?php


namespace App\Http\Controllers\Admin\Base;


use App\Http\Controllers\Controller;
use App\Services\Admin\Base\AdminMenuService;
use App\Utils\Result;
use Exception;
use Illuminate\Http\JsonResponse;

class AdminMenuController extends Controller
{
    private $adminMenuService;

    /**
     * AdminMenuController constructor.
     */
    public function __construct(AdminMenuService $adminMenuService)
    {
        $this->adminMenuService = $adminMenuService;
    }


    /**
     * 菜单列表
     * @return JsonResponse
     * author Yan
     */
    public function index(): JsonResponse
    {
        $data = $this->adminMenuService->listAll();

        return Result::success($data);
    }

    /**
     * 所有列表
     * @return JsonResponse
     * author Yan
     */
    public function listAll(): JsonResponse
    {
        $data = $this->adminMenuService->listTop();

        return Result::success($data);
    }

    /**
     * 菜单添加
     * @return JsonResponse
     * author Yan
     */
    public function store(): JsonResponse
    {
        $params = request()->all();

        $result = $this->adminMenuService->create($params);

        return Result::choose($result);
    }

    /**
     * 菜单更新
     * @return JsonResponse
     * author Yan
     */
    public function update($id): JsonResponse
    {
        $params = request()->all();

        $result = $this->adminMenuService->update($id, $params);

        return Result::choose($result);
    }

    /**
     * 删除菜单
     * @param $id
     * @return JsonResponse
     * author Yan
     * @throws Exception
     */
    public function destroy($id): JsonResponse
    {
        $result = $this->adminMenuService->delete($id);

        return Result::choose($result);
    }

}
