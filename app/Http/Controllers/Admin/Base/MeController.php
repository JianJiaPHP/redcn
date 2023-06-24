<?php


namespace App\Http\Controllers\Admin\Base;


use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Http\Requests\MePwdRequests;
use App\Http\Requests\MeUuidRequests;
use App\Models\News\News;
use App\Services\Admin\Base\AdministratorService;
use App\Utils\Result;
use Illuminate\Http\JsonResponse;

class MeController extends Controller
{
    private $administratorService;
    private $adminMenuService;

    /**
     * MeController constructor.
     */
    public function __construct(
        AdministratorService                      $administratorService,
        \App\Services\Admin\Base\AdminMenuService $adminMenuService
    )
    {
        $this->administratorService = $administratorService;
        $this->adminMenuService = $adminMenuService;
    }


    /**
     * User: Yan
     * DateTime: 2023/3/7
     * @return JsonResponse 首页数据统计
     * 首页数据统计
     */
    public function homeData(): JsonResponse
    {
        $id = auth('api')->id();

        $data = $this->administratorService->homeData($id);

        return Result::success($data);
    }


    /**
     * 修改密码
     * @param MePwdRequests $mePwdRequests
     * @return JsonResponse
     * @throws ApiException
     */
    public function updatePwd(MePwdRequests $mePwdRequests): JsonResponse
    {
        $params = $mePwdRequests->validated();
        $result = $this->administratorService->updatePwd($params);

        return Result::choose($result);

    }

    /**
     * 修改uuid
     * @param MeUuidRequests $meUuidRequests
     * @return JsonResponse
     */
    public function updateUuid(MeUuidRequests $meUuidRequests): JsonResponse
    {
        $params = $meUuidRequests->validated();
        $result = $this->administratorService->updateUuid($params);

        return Result::choose($result);

    }

    /**
     * 更新个人信息
     * @return JsonResponse
     * author Yan
     * @throws ApiException
     */
    public function updateInfo(): JsonResponse
    {
        $params = request()->all();
        $result = $this->administratorService->updateInfo($params);
        return Result::choose($result);
    }

    /**
     * 获取菜单
     * @return JsonResponse
     * author Yan
     */
    public function getNav(): JsonResponse
    {
        $data = $this->adminMenuService->getMenu();

        return Result::success($data);
    }

    /**
     * 查看个人信息
     * @return JsonResponse
     */
    public function me(): JsonResponse
    {
        $data = $this->administratorService->getInfo();
        return Result::success($data);
    }

    /**
     * 退出登录
     * @return JsonResponse
     */
    public function logout(): JsonResponse
    {
        auth('api')->logout();

        return Result::success();
    }

    /**
     * User: Yan
     * DateTime: 2023/5/19
     * @return JsonResponse 新闻接口
     * 新闻接口
     * @throws ApiException
     */
    public function newsGet(): JsonResponse
    {
        $params = request()->all();
        # 验证器
        $validator = validator($params, [
            'id' => 'required|integer',
        ], [
            'id.required' => '协议id不能为空',
            'id.integer'  => '协议id必须为整数',
        ]);
        if ($validator->fails()) {
            throw new ApiException($validator->errors()->first());
        }
        $data = News::query()->where('id',$params['id'])->first();
        return Result::success($data);
    }
}
