<?php


namespace App\Http\Controllers\Admin\Prompt;


use App\Http\Controllers\Controller;
use App\Services\Admin\Prompt\MidJourneyPromptService;
use App\Utils\Result;
use Exception;
use Illuminate\Http\JsonResponse;

class PromptMidJourneyController extends Controller
{

    private MidJourneyPromptService $promptService;

    /**
     * ClassService constructor.
     */
    public function __construct(MidJourneyPromptService $promptService)
    {
        $this->promptService = $promptService;
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

        $data = $this->promptService->dataTree($params);

        return Result::success($data);
    }


    public function listAll(): JsonResponse
    {
        $params = request()->all();

        $data = $this->promptService->dataTree($params);

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
        $data = $this->promptService->destroy($id);

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

        $res = $this->promptService->create($params);

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
        $res = $this->promptService->update($id, $params);

        if ($res['code'] != 200) {
            return Result::fail($res['msg']);
        }
        return Result::success();
    }


}
