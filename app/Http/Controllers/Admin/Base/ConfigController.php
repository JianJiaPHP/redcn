<?php


namespace App\Http\Controllers\Admin\Base;


use App\Http\Controllers\Controller;
use App\Models\NewD;
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
     */
    public function getAll(): JsonResponse
    {
        $data = $this->configService->getAll();

        return Result::success($data);
    }

    /**
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

    # 获取富文本数据
    public function getRichText():JsonResponse
    {
        $params = request()->all();
        # 验证器
        $validator = Validator::make($params, [
            # key 必传
            'key' => 'required',
        ], [
            'key.required' => 'key不能为空',
        ]);
        # 验证失败
        if ($validator->fails()) {
            return Result::fail($validator->errors()->first());
        }
        $value = NewD::query()->where('key_name',$params['key'])->value('content_text');
        return Result::success($value);
    }
}
