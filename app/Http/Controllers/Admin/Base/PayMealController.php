<?php


namespace App\Http\Controllers\Admin\Base;


use App\Http\Controllers\Controller;
use App\Services\Base\PayMealService;
use App\Utils\Result;
use Exception;
use Illuminate\Http\JsonResponse;

class PayMealController extends Controller
{

    protected $payMeal;

    public function __construct(PayMealService $payMeal)
    {
        $this->payMeal = $payMeal;
    }


    /**
     * User: Yan
     * DateTime: 2023/3/7
     * @return JsonResponse
     * 列表
     */
    public function index(): JsonResponse
    {
        $params = request()->all();

        $data = $this->payMeal->list($params);

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
        $data = $this->payMeal->destroy($id);

        return Result::choose($data);
    }

    /**
     * User: Yan
     * DateTime: 2023/3/7
     * @return JsonResponse
     * 新增
     */
    public function store(): JsonResponse
    {
        $params = request()->all();

        $this->payMeal->create($params);

        return Result::success();
    }

    /**
     * 修改
     * @param $id int
     * @return JsonResponse
     * author Yan
     * @throws Exception
     */
    public function update(int $id): JsonResponse
    {
        $params = request()->all();

        $this->payMeal->update($id, $params);

        return Result::success();
    }

    /**
     * User: Yan
     * DateTime: 2023/3/7
     * @return JsonResponse
     * 查询列表
     */
    public function listInfo(): JsonResponse
    {
        $params = request()->all();

        $data = $this->payMeal->listInfo($params);

        return Result::success($data);
    }

}
