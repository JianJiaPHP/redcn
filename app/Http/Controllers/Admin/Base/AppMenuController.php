<?php


namespace App\Http\Controllers\Admin\Base;


use App\Http\Controllers\Controller;
use App\Http\Requests\MenuRequests;
use App\Utils\Result;
use Exception;
use Illuminate\Http\JsonResponse;

class AppMenuController extends Controller
{
    private $appMenuService;

    /**
     * AdminMenuController constructor.
     */
    public function __construct(\App\Services\Admin\Base\AppMenuService $appMenuService)
    {
        $this->appMenuService = $appMenuService;
    }


    /**
     * 菜单列表
     * @return JsonResponse
     * author Yan
     */
    public function index(): JsonResponse
    {

        $data = $this->appMenuService->listAll();

        return Result::success($data);
    }

    /**
     * 所有列表
     * @return JsonResponse
     * author Yan
     */
    public function listAll(): JsonResponse
    {
        $data = $this->appMenuService->listTop();

        return Result::success($data);
    }

    /**
     * 菜单添加
     * @return JsonResponse
     * author Yan
     */
    public function store(MenuRequests $requests): JsonResponse
    {
        $params = $requests->all();

        $result = $this->appMenuService->create($params);

        return Result::choose($result);
    }

    /**
     * 菜单更新
     * @return JsonResponse
     * author Yan
     */
    public function update(MenuRequests $requests, $id): JsonResponse
    {
        $params = $requests->all();

        $result = $this->appMenuService->update($id, $params);

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
        $result = $this->appMenuService->delete($id);

        return Result::choose($result);
    }

}
