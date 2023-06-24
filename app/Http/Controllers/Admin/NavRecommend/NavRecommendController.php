<?php


namespace App\Http\Controllers\Admin\NavRecommend;


use App\Http\Controllers\Controller;
use App\Services\Admin\NavRecommend\NavRecommendService;
use App\Utils\Result;
use Exception;
use Illuminate\Http\JsonResponse;

class NavRecommendController extends Controller
{

    private NavRecommendService $navRecommendService;

    /**
     * ClassService constructor.
     */
    public function __construct(NavRecommendService $navRecommendService)
    {
        $this->navRecommendService = $navRecommendService;
    }


    /**
     * User: Yan
     * DateTime: 2023/3/7
     * @return JsonResponse
     * 查询 新闻列表
     */
    public function index(): JsonResponse
    {
        $params = request()->all();

        $data = $this->navRecommendService->dataTree($params);

        return Result::success($data);
    }

    /**
     * 返回所有
     * @return JsonResponse
     * @Time 2023/5/23 17:20
     * @author sunsgne
     */
    public function listAll(): JsonResponse
    {
        $params = request()->all();

        $data   = $this->navRecommendService->dataTree($params);
        $data[] = [
            'p_id'  => 0,
            'title' => '顶级',
            'level' => 0,
            'id'    => 0
        ];
        return Result::success($data);
    }

    /**
     * User: Yan
     * DateTime: 2023/3/7
     * @param int $id
     * @return JsonResponse
     * 删除 新闻分类
     */
    public function destroy(int $id): JsonResponse
    {
        //查看分类下面是否还有商户
        $data = $this->navRecommendService->destroy($id);

        return Result::choose($data);
    }

    /**
     * User: Yan
     * DateTime: 2023/3/7
     * @return JsonResponse
     * 新增新闻分类
     */
    public function store(): JsonResponse
    {
        $params = request()->all();

        $res = $this->navRecommendService->create($params);

        if ($res['code'] != 200) {
            return Result::fail($res['msg']);
        }
        return Result::success();
    }

    /**
     * User: Yan
     * DateTime: 2023/3/7
     * @param int $id
     * @return JsonResponse
     * 更新新闻分类
     * @throws Exception
     */
    public function update(int $id): JsonResponse
    {
        $params = request()->all();

        unset($params['children']);
        $res = $this->navRecommendService->update($id, $params);

        if ($res['code'] != 200) {
            return Result::fail($res['msg']);
        }
        return Result::success();
    }


}
