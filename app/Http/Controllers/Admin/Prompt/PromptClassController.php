<?php


namespace App\Http\Controllers\Admin\Prompt;


use App\Http\Controllers\Controller;
use App\Models\Prompt\Prompt;
use App\Services\Admin\Prompt\PromptClassService;
use App\Utils\Result;
use Exception;
use Illuminate\Http\JsonResponse;

class PromptClassController extends Controller
{

    private PromptClassService  $promptClassService;

    /**
     * ClassService constructor.
     */
    public function __construct(PromptClassService $promptClassService)
    {
        $this->promptClassService = $promptClassService;
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

        $data = $this->promptClassService->list($params);

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
        $exists = Prompt::query()->where(['class_id' => $id])->exists();
        if ($exists) {
            return Result::fail("该分类下还有新闻");
        }
        $data = $this->promptClassService->destroy($id);

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

        $this->promptClassService->create($params);

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

        $this->promptClassService->update($id, $params);

        return Result::success();
    }


}
