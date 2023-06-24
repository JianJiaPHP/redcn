<?php


namespace App\Http\Controllers\Admin\Base;


use App\Http\Controllers\Controller;
use App\Utils\Result;
use Exception;
use Illuminate\Http\JsonResponse;

class AdminResourceController extends Controller
{
    private $adminResourceService;

    /**
     * AdminResourceController constructor.
     */
    public function __construct(\App\Services\Admin\Base\AdminResourceService $adminResourceService)
    {
        $this->adminResourceService = $adminResourceService;
    }


    /**
     * 列表
     * @return JsonResponse
     * author Yan
     */
    public function index(): JsonResponse
    {
        $name = request()->query('name');
        $url = request()->query('url');
        $limit = request()->query('limit', 10);
        $data = $this->adminResourceService->list($name,$url, $limit);

        return Result::success($data);
    }

    /**
     * 添加
     * @return JsonResponse
     * author Yan
     */
    public function store(): JsonResponse
    {
        $params = request()->all();
        $this->adminResourceService->create($params);

        return Result::success();
    }

    /**
     * 获取所有的资源
     * @return JsonResponse
     * author Yan
     */
    public function all(): JsonResponse
    {
        $data = $this->adminResourceService->all();

        return Result::success($data);
    }

    /**
     * User: Yan
     * DateTime: 2023/2/22
     * @return JsonResponse
     * 根据我当前用户的角色获取所有的资源
     */
    public function adminAll(): JsonResponse
    {
        $data = $this->adminResourceService->adminAll();

        return Result::success($data);
    }


    /**
     * 更新
     * @return JsonResponse
     * author Yan
     */
    public function update($id): JsonResponse
    {
        $params = request()->all();

        $result = $this->adminResourceService->update($id, $params);

        return Result::choose($result);
    }

    /**
     * 删除
     * @param $id
     * @return JsonResponse
     * author Yan
     * @throws Exception
     */
    public function destroy($id): JsonResponse
    {
        $result = $this->adminResourceService->delete($id);

        return Result::choose($result);
    }
}
