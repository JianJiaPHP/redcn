<?php


namespace App\Http\Controllers\Admin\Base;


use App\Http\Controllers\Controller;
use App\Services\AppConfigService;
use App\Utils\Result;
use Illuminate\Http\JsonResponse;

class AppConfigController extends Controller
{

    private $configService;

    /**
     * ConfigController constructor.
     */
    public function __construct(AppConfigService $configService)
    {
        $this->configService = $configService;
    }


    /**
     * User: Yan
     * DateTime: 2023/3/7
     * @return JsonResponse
     * 获取APP配置信息
     */
    public function index(): JsonResponse
    {
        $data = $this->configService->list();

        return Result::success($data);
    }


    /**
     * 修改配置信息
     * @return JsonResponse
     * @author Yan
     */
    public function update(): JsonResponse
    {
        $params = request()->all();

        $this->configService->update($params);

        return Result::success();
    }


}
