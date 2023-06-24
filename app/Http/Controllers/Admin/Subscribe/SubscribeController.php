<?php


namespace App\Http\Controllers\Admin\Subscribe;


use App\Http\Controllers\Controller;
use App\Services\Admin\Subscribe\SubscribeService;
use App\Utils\Result;
use Exception;
use Illuminate\Http\JsonResponse;

/**
 * 订单数据控制器
 * @Time 2023/5/19 11:09
 * @author sunsgne
 */
class SubscribeController extends Controller
{
    private SubscribeService $subscribeService;

    /**
     * ClassService constructor.
     */
    public function __construct(SubscribeService $subscribeService)
    {
        $this->subscribeService = $subscribeService;
    }


    /**
     * 根据条件获取订单列表
     * User: Yan
     * DateTime: 2023/3/7
     * @return JsonResponse
     *
     */
    public function index(): JsonResponse
    {
        $params = request()->all();

        $data = $this->subscribeService->list($params);

        return Result::success($data);
    }

    /**
     * 删除
     * User: Yan
     * DateTime: 2023/3/7
     * @param int $id
     * @return JsonResponse
     * 删除
     */
//    public function destroy(int $id): JsonResponse
//    {
//        //查看分类下面是否还有商户
//        $data = $this->subscribeService->destroy($id);
//
//        return Result::choose($data);
//    }
//
//
//    /**
//     * User: Yan
//     * DateTime: 2023/3/7
//     * @param int $id
//     * @return JsonResponse
//     * 更新
//     * @throws Exception
//     */
//    public function update(int $id): JsonResponse
//    {
//        $params = request()->all();
//
//        $res = $this->subscribeService->update($id, $params);
//
//        if ($res['code'] != 200) {
//            return Result::fail($res['msg']);
//        }
//        return Result::success();
//    }


}
