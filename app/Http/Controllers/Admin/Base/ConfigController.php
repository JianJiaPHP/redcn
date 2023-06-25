<?php


namespace App\Http\Controllers\Admin\Base;


use App\Http\Controllers\Controller;
use App\Services\Admin\Base\ConfigService;
use App\Utils\Result;
use Illuminate\Http\JsonResponse;
use Validator;

class ConfigController extends Controller
{

    private $configService;

    /**
     * ConfigController constructor.
     */
    public function __construct(ConfigService $configService)
    {
        $this->configService = $configService;
    }


    /**
     * 获取配置信息
     * @return JsonResponse
     * @author Aii
     * @date 2019/12/13 下午3:22
     */
    public function index(): JsonResponse
    {
        $data = $this->configService->list();

        return Result::success($data);
    }


    /**
     * 修改配置信息
     * @param $id int 配置id
     * @return JsonResponse
     * @author Aii
     * @date 2019/12/13 下午3:24
     */
    public function update(int $id): JsonResponse
    {
        $params = request()->all();

        $this->configService->update($id, $params['value']);

        return Result::success();
    }


    /**
     * 根据key值获取
     * @return JsonResponse
     * author Yan
     */
    public function getOne(): JsonResponse
    {
        $params = request()->all();
        $key = $params['key'];
        if (empty($key)) {
            return Result::fail('参数错误');
        }
        $data = $this->configService->getOne($key);

        return Result::success($data);
    }

    /**
     * 根据key值获取
     * @param $key
     * @return JsonResponse
     * author Yan
     */
    public function getAll(): JsonResponse
    {
        $data = $this->configService->getAll();

        return Result::success($data);
    }

    /**
     * User: Yan
     * DateTime: 2023/4/11
     * @return JsonResponse 获取小程序资质
     * 获取小程序资质
     */
    public function getMiniProve(): JsonResponse
    {
        $data = $this->configService->getMiniProve();

        return Result::success($data);
    }

    public function postMiniProve(): JsonResponse
    {
        $params = request()->all();
        #验证器判断list是否为空
        $validator = Validator::make($params, [
            # list 必传 数组
            'list' => 'required|array',
        ], [
            'list.required' => '资质图片不能为空',
            'list.array'    => '资质图片必须为数组',
        ]);
        # 验证失败
        if ($validator->fails()) {
            return Result::fail($validator->errors()->first());
        }
        $data = $this->configService->postMiniProve($params['list']);

        return Result::success($data);
    }

}
