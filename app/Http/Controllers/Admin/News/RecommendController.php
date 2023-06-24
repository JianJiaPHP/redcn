<?php


namespace App\Http\Controllers\Admin\News;


use App\Http\Controllers\Controller;
use App\Http\Requests\RecommendCreateRequests;
use App\Services\Admin\News\RecommendService;
use App\Utils\Result;
use Illuminate\Http\JsonResponse;

class RecommendController extends Controller
{

    private RecommendService $service;

    /**
     * ClassService constructor.
     */
    public function __construct(RecommendService $service)
    {
        $this->service = $service;
    }


    /**
     * User: Yan
     * DateTime: 2023/3/6
     * @return JsonResponse
     * 推荐位列表
     */
    public function index(): JsonResponse
    {
        $params = request()->all();
        $data = $this->service->list($params);

        return Result::success($data);
    }

    /**
     * User: Yan
     * DateTime: 2023/3/7
     * @param int $id
     * @return JsonResponse
     * 删除推荐位
     */
    public function destroy(int $id): JsonResponse
    {
        $data = $this->service->destroy($id);

        return Result::choose($data);
    }

    /**
     * User: Yan
     * DateTime: 2023/3/7
     * @return JsonResponse
     * 新增推荐位
     */
    public function store(RecommendCreateRequests $requests): JsonResponse
    {
        $params = $requests->all();
        try {
            $res = $this->service->create($params);

            if ($res['code'] != 200) {
                return Result::fail($res['msg']);
            }
            return Result::success();
        }catch (\Exception $e)
        {
            return Result::fail($e->getMessage());
        }
    }

    /**
     * User: Yan
     * DateTime: 2023/3/22
     * @param int $id
     * @param RecommendCreateRequests $requests
     * @return JsonResponse
     * 更新推荐位
     */
    public function update(int $id, RecommendCreateRequests $requests): JsonResponse
    {
        $params = $requests->all();
        if (!$id) {
            return Result::fail('参数错误缺少ID');
        }
        try {
            $res = $this->service->update($id, $params);

            if ($res['code'] != 200) {
                return Result::fail($res['msg']);
            }
            return Result::success();
        }catch (\Exception $e)
        {
            return Result::fail($e->getMessage());
        }

    }


}
