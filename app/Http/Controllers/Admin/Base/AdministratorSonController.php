<?php


namespace App\Http\Controllers\Admin\Base;


use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Models\Base\AdminRole;
use App\Services\Admin\Base\AdministratorSonService;
use App\Utils\Result;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;


# 商户号/导师号 子账号
class AdministratorSonController extends Controller
{
    private AdministratorSonService $administratorSonService;
    protected array $role = [];//0:管理员 1:商户 2:导师

    /**
     * AdministratorController constructor.
     */
    public function __construct(\App\Services\Admin\Base\AdministratorSonService $administratorSonService)
    {
        if (!auth('api')->user()) {
            return Result::unauthorized();
        }
        # 获取当前账号是否是商户号/导师号
        if (auth('api')->user()['is_store'] > 0) {
            $this->role = ['role'=>AdminRole::ROLE_STORE,'id'=>auth('api')->user()['is_store']];//商户
        }elseif (auth('api')->user()['tutor_id'] > 0) {
            $this->role = ['role'=>AdminRole::ROLE_TUTOR,'id'=>auth('api')->user()['tutor_id']];//导师
        }
        $this->administratorSonService = $administratorSonService;
    }


    /**
     * 管理员列表
     * @param Request $request
     * @return JsonResponse
     * author Yan
     */
    public function index(Request $request): JsonResponse
    {
        $account = $request->query('account', '');
        $limit = $request->query('limit', 10);
        try {
            $list = $this->administratorSonService->list($account, $limit, $this->role);
            return Result::success($list);
        } catch (ApiException $e) {
            return Result::fail($e->getMessage());
        }
    }

    /**
     * 添加管理员
     * @return JsonResponse
     * @throws ApiException
     * @author Yan
     */
    public function store(): JsonResponse
    {
        $params = request()->all();

        $result = $this->administratorSonService->add($params,$this->role);

        return Result::choose($result);

    }

    /**
     * 管理更新
     * @param $id
     * @return JsonResponse
     * @throws Exception
     * @author Yan
     */
    public function update($id): JsonResponse
    {
        $params = request()->all();
        $result = $this->administratorSonService->update($id, $params);

        return Result::choose($result);
    }

    /**
     * 管理员删除
     * @param $id
     * @return JsonResponse
     * @throws Exception
     * @author Yan
     */
    public function destroy($id): JsonResponse
    {
        $result = $this->administratorSonService->destroy($id);

        return Result::choose($result);
    }


}
