<?php


namespace App\Http\Controllers\Admin\News;


use App\Http\Controllers\Controller;
use App\Models\News\News;
use App\Services\Admin\News\NewsClassService;
use App\Utils\Result;
use Exception;
use Illuminate\Http\JsonResponse;

class NewsClassController extends Controller
{

    private NewsClassService $newsClassService;

    /**
     * ClassService constructor.
     */
    public function __construct(NewsClassService $newsClassService)
    {
        $this->newsClassService = $newsClassService;
    }


    /**
     * User: Yan
     * DateTime: 2023/3/7
     * @return JsonResponse
     * 查询新闻分类列表
     */
    public function index(): JsonResponse
    {
        $params = request()->all();

        $data = $this->newsClassService->list($params);

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
        $exists = News::query()->where(['class_id' => $id])->exists();
        if ($exists) {
            return Result::fail("该分类下还有新闻");
        }
        $data = $this->newsClassService->destroy($id);

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

        $this->newsClassService->create($params);

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

        $this->newsClassService->update($id, $params);

        return Result::success();
    }


}
