<?php


namespace App\Http\Controllers\Admin\Base;


use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Services\Admin\Base\AdministratorService;
use App\Utils\Result;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Validator;

class AdministratorController extends Controller
{
    private $administratorService;

    /**
     * AdministratorController constructor.
     */
    public function __construct(AdministratorService $administratorService)
    {
        $this->administratorService = $administratorService;
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
        $list = $this->administratorService->list($account, $limit);
        return Result::success($list);
    }

    /**
     * 添加管理员
     * @return JsonResponse
     * @throws ApiException
     * @author Aii
     */
    public function store(): JsonResponse
    {
        # 字段验证
        $params = request()->all();

        # 验证器
        $validator = Validator::make($params, [
            # 最长20字符
            'account'  => 'required|string|max:20',
            # 最长20个字符
            'nickname' => 'required|string|max:20',
            #密码 非必填
            'password' => 'string|max:30',
            # role_ids
            'roleIds'  => 'required',
        ], [
            'account.required'  => '账号不能为空',
            'account.string'    => '账号必须是字符串',
            'account.max'       => '账号最长20个字符',
            'nickname.required' => '昵称不能为空',
            'nickname.string'   => '昵称必须是字符串',
            'nickname.max'      => '昵称最长20个字符',
            'avatar.string'     => '头像必须是字符串',
            'password.string'   => '密码必须是字符串',
            'password.max'      => '密码最长30个字符',
            'roleIds.required'  => '角色不能为空',
        ]);
        if ($validator->fails()) {
            return Result::fail($validator->errors()->first());
        }


        $result = $this->administratorService->add($params);

        return Result::success($result, '添加成功');

    }

    /**
     * 管理更新
     * @param $id
     * @return JsonResponse
     * @throws Exception
     * @author Aii
     */
    public function update($id): JsonResponse
    {
        $params = request()->all();
        # 验证器
        $validator = Validator::make($params, [
            # 最长20字符
            'account'  => 'required|string|max:20',
            # 最长20个字符
            'nickname' => 'required|string|max:20',
            # role_ids
            'roleIds'  => 'required',
        ], [
            'account.required'  => '账号不能为空',
            'account.string'    => '账号必须是字符串',
            'account.max'       => '账号最长20个字符',
            'nickname.required' => '昵称不能为空',
            'nickname.string'   => '昵称必须是字符串',
            'nickname.max'      => '昵称最长20个字符',
            'avatar.string'     => '头像必须是字符串',
            'roleIds.required'  => '角色不能为空',
        ]);
        if ($validator->fails()) {
            return Result::fail($validator->errors()->first());
        }
        $result = $this->administratorService->update($id, $params);

        return Result::success($result, '更新成功');
    }

    /**
     * 管理员删除
     * @param $id
     * @return JsonResponse
     * @throws Exception
     * @author Aii
     */
    public function destroy($id): JsonResponse
    {
        $result = $this->administratorService->destroy($id);

        return Result::success($result,'删除成功');
    }


}
