<?php


namespace App\Http\Controllers\Admin\Goods;


use App\Http\Controllers\Controller;
use App\Services\Admin\Goods\GoodsClassService;
use App\Utils\Result;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GoodsClassController extends Controller
{
    private GoodsClassService $goodsClassService;

    /**
     * WithdrawalController constructor.
     */
    public function __construct(GoodsClassService $goodsClassService)
    {
        $this->goodsClassService = $goodsClassService;
    }


    public function index(Request $request): JsonResponse
    {
        $data = $request->all();

        $list = $this->goodsClassService->list($data);

        return Result::success($list);
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

        $res = $this->goodsClassService->update($id, $params);

        if ($res['code'] != 200) {
            return Result::fail($res['msg']);
        }
        return Result::success();
    }


}
