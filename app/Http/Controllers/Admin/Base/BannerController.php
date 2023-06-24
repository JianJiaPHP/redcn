<?php

/**
 * Banner
 */

namespace App\Http\Controllers\Admin\Base;


use App\Http\Controllers\Controller;
use App\Http\Requests\BannerCreateRequests;
use App\Services\Admin\Base\BannerService;
use App\Utils\Result;
use Exception;
use Illuminate\Http\JsonResponse;

class BannerController extends Controller
{

    private BannerService $service;//Banner分类

    /**
     * PlatformController constructor.
     */
    public function __construct(BannerService $service)
    {
        $this->service = $service;
    }


    /**
     * User: Yan
     * DateTime: 2023/3/7
     * @return JsonResponse
     * 查询
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
     * 删除
     */
    public function destroy(int $id): JsonResponse
    {
        $data = $this->service->destroy($id);

        return Result::choose($data);
    }


    /**
     * User: Yan
     * DateTime: 2023/2/23
     * @param BannerCreateRequests $requests
     * @return JsonResponse
     * @throws Exception
     * 新增
     */
    public function store(BannerCreateRequests $requests): JsonResponse
    {
        $params = $requests->all();

        $res = $this->service->create($params);

        if ($res['code'] != 200) {
            return Result::fail($res['msg']);
        }
        return Result::success();
    }


    /**
     * User: Yan
     * DateTime: 2023/2/23
     * @param BannerCreateRequests $requests
     * @param int $id
     * @return JsonResponse
     * 修改
     */
    public function update(BannerCreateRequests $requests,int $id): JsonResponse
    {
        try {
            $params = $requests->all();

            $res = $this->service->update($id, $params);

            if ($res['code'] != 200) {
                return Result::fail($res['msg']);
            }
            return Result::success();
        }catch (Exception $e){
            return Result::fail($e->getMessage());
        }

    }


}
