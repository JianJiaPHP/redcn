<?php


namespace App\Http\Controllers\Admin\Feedback;


use App\Http\Controllers\Controller;
use App\Services\Admin\Feedback\FeedBackService;
use App\Utils\Result;
use Illuminate\Http\JsonResponse;

class FeedbackController extends Controller
{
    private FeedBackService $feedBackService;

    /**
     * ClassService constructor.
     */
    public function __construct(FeedBackService $feedBackService)
    {
        $this->feedBackService = $feedBackService;
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

        $data = $this->feedBackService->list($params);

        return Result::success($data);
    }


}
