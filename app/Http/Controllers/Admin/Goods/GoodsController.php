<?php


namespace App\Http\Controllers\Admin\Goods;


use App\Http\Controllers\Controller;
use App\Utils\Result;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use function PHPUnit\Framework\isJson;

class GoodsController extends Controller
{
    private \App\Services\Admin\Goods\GoodsService $goodsService;

    /**
     * WithdrawalController constructor.
     */
    public function __construct(\App\Services\Admin\Goods\GoodsService $goodsService)
    {
        $this->goodsService = $goodsService;
    }


    public function index(Request $request): JsonResponse
    {
        $data = $request->all();

        $list = $this->goodsService->list($data);

        return Result::success($list);
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
        $data = $this->goodsService->destroy($id);

        return Result::choose($data);
    }


    /**
     * 新增
     * @param Request $request
     * @return JsonResponse
     * @Time 2023/6/2 15:44
     * @author sunsgne
     */
    public function add(Request $request): JsonResponse
    {
        $data = $request->all();

        $id = $this->goodsService->add($data);

        return Result::success(['id' => $id]);
    }

    /**
     * 修改
     * @param int $id
     * @return JsonResponse
     * @Time 2023/5/19 14:13
     * @author sunsgne
     */
    public function update(int $id): JsonResponse
    {
        $params = request()->all();

        $res = $this->goodsService->update($id, $params);

        if ($res['code'] != 200) {
            return Result::fail($res['msg']);
        }
        return Result::success();
    }


}
