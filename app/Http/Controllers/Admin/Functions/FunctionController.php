<?php


namespace App\Http\Controllers\Admin\Functions;


use App\Http\Controllers\Controller;
use App\Services\Admin\Functions\FunctionService;
use App\Utils\Result;
use Illuminate\Http\JsonResponse;

class FunctionController extends Controller
{
    private FunctionService $functionService;

    /**
     * ClassService constructor.
     */
    public function __construct(FunctionService $functionService)
    {
        $this->functionService = $functionService;
    }

    /**
     * User: Yan
     * DateTime: 2023/3/7
     * @return JsonResponse
     * 查询列表
     */
    public function index(): JsonResponse
    {
        $params = request()->all();

        $data = $this->functionService->list($params);

        return Result::success($data);
    }


    /**
     * User: Yan
     * DateTime: 2023/3/7
     * @return JsonResponse
     * 查询列表
     */
    public function update(int $id ): JsonResponse
    {
        $params = request()->all();

        $data = $this->functionService->update($id , $params);

        return Result::success($data);
    }


}
