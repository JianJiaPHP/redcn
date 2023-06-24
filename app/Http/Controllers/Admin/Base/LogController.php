<?php


namespace App\Http\Controllers\Admin\Base;


use App\Http\Controllers\Controller;
use App\Services\Admin\Base\LogService;
use App\Utils\Result;
use Illuminate\Http\JsonResponse;

class LogController extends Controller
{
    private $service;

    /**
     * LogController constructor.
     */
    public function __construct(LogService $service)
    {
        $this->service = $service;
    }


    /**
     * 操作日志列表
     * @return JsonResponse
     * @author Aii
     * @date 2019/12/13 下午4:05
     */
    public function operatingLog(): JsonResponse
    {
        $params = request()->all();
        $keyword = $params['keyword'];
        $limit = $params['limit'];

        $data = $this->service->operatingLog($keyword, $limit);

        return Result::success($data);
    }

    /**
     * 登陆日志列表
     * @return JsonResponse
     * @author Aii
     * @date 2019/12/19 下午2:44
     */
    public function loginLog(): JsonResponse
    {
        $params = request()->all();
        $keyword = $params['keyword'];
        $limit = $params['limit'];

        $data = $this->service->loginLog($keyword, $limit);

        return Result::success($data);
    }

}
