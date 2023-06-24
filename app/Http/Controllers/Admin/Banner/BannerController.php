<?php


namespace App\Http\Controllers\Admin\Banner;


use App\Http\Controllers\Controller;
use App\Services\Admin\Banner\BannerService;
use App\Utils\Result;
use Exception;
use Illuminate\Http\JsonResponse;

class BannerController extends Controller
{

    private BannerService $bannerService;

    /**
     * ClassService constructor.
     */
    public function __construct(BannerService $bannerService)
    {
        $this->bannerService = $bannerService;
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

        $data = $this->bannerService->list($params);

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
        $data = $this->bannerService->destroy($id);

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

        $res = $this->bannerService->create($params);

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

        $res = $this->bannerService->update($id, $params);

        if ($res['code'] != 200) {
            return Result::fail($res['msg']);
        }
        return Result::success();
    }


}
