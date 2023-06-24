<?php

namespace App\Http\Controllers\Admin\Users;

use App\Http\Controllers\Controller;
use App\Services\Admin\Users\UsersServices;
use App\Utils\Result;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\Exception as PhpSpreadsheetException;
use Validator;

class UsersController extends Controller
{

    protected UsersServices $usersServices;

    public function __construct(\App\Services\Admin\Users\UsersServices $usersServices)
    {
        $this->usersServices = $usersServices;
    }

    /**
     * User: Yan
     * DateTime: 2023/2/24
     * @return JsonResponse
     * 用户列表
     */
    public function index(): JsonResponse
    {
        $params = request()->all();

        $data = $this->usersServices->userList($params);

        return Result::success($data);
    }


    /**
     * 获取用户资金
     * @param $id
     * @return JsonResponse
     * @Time 2023/5/19 11:43
     * @author sunsgne
     */
    public function account($id): JsonResponse
    {
        $params = request()->all();
        $data   = $this->usersServices->account($id);
        return Result::success($data);
    }

    /**
     * 根据ID获取模型
     * @param $id
     * @return JsonResponse
     * @Time 2023/5/19 11:47
     * @author sunsgne
     */
    public function prompt($id): JsonResponse
    {
        $params = request()->all();
        $data   = $this->usersServices->prompt($id);
        return Result::success($data);
    }

    /**
     * 根据用户ID获取订阅
     * @param $id
     * @return JsonResponse
     * @Time 2023/5/19 11:47
     * @author sunsgne
     */
    public function subscribe($id): JsonResponse
    {
        $params = request()->all();
        $data   = $this->usersServices->subscribe($id);
        return Result::success($data);
    }


    /**
     * User: Yan
     * DateTime: 2023/2/24
     * @return JsonResponse
     * 修改
     */
    public function update($id): JsonResponse
    {
        $params = request()->all();
        try {
            $data = $this->usersServices->update($id, $params);
            return Result::choose($data);
        } catch (Exception $e) {
            return Result::fail($e->getMessage());
        }

    }

    /**
     * 给用户赠送订阅
     * @param $id
     * @return JsonResponse
     * @Time 2023/5/22 13:42
     * @author sunsgne
     */
    public function giveAwaySubscribe($id): JsonResponse
    {
        $params = request()->all();
        try {
            # 验证器
            $validator = validator($params, [
                'goods_id'    => 'required|numeric|min:1',
                'token_total' => 'required|numeric',
                'token'       => 'required|numeric',
                'end_date'    => 'required|string',
            ], [
                'goods_id.required'    => '商品不能为空',
                'token_total.required' => 'token_total不能为空',
                'token.required'       => 'token不能为空',
                'end_date.required'    => '过期时间不能为空',
                'goods_id.numeric'     => '商品ID必须为数字',
            ]);
            if ($validator->fails()) {
                return Result::fail($validator->errors()->first());
            }

            if ($this->usersServices->giveAwaySubscribe($id, $params)){
                return Result::choose(['code' => 200 , 'msg' => '赠送成功']);
            }
            return Result::fail('赠送失败');
        } catch (Exception $e) {
            return Result::fail($e->getMessage());
        }

    }

    /**
     * 获取用户的下级用户
     * @param $id
     * @return JsonResponse
     * @Time 2023/5/24 17:39
     * @author sunsgne
     */
    public function subsetUserList($id): JsonResponse
    {
        $data   = $this->usersServices->GetSubsetUserList($id);
        return Result::success($data);
    }


    /**
     * User: Yan
     * DateTime: 2023/2/24
     * @param Request $request
     * @return JsonResponse
     * 用户登录日志
     */
    public function loginLog(Request $request): JsonResponse
    {
        # 验证user_id必传参
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
        ], [
            'user_id.required' => '用户id必传参',
        ]);
        if ($validator->fails()) {
            return Result::fail($validator->errors()->first());
        }
        try {
            $validated = $validator->validated();
        } catch (ValidationException $e) {
            return Result::fail($e->getMessage());
        }
        $data = $this->usersServices->loginLog($validated['user_id']);
        return Result::success($data);
    }

    /**
     * User: Yan
     * DateTime: 2023/2/24
     * @return JsonResponse
     * 用户行为日志
     */
    public function usersLog(): JsonResponse
    {
        # 验证user_id必传参
        $validator = Validator::make(request()->all(), [
            'user_id' => 'required',
        ], [
            'user_id.required' => '用户id必传参',
        ]);
        if ($validator->fails()) {
            return Result::fail($validator->errors()->first());
        }
        try {
            $validated = $validator->validated();
        } catch (ValidationException $e) {
            return Result::fail($e->getMessage());
        }
        $data = $this->usersServices->usersLog($validated['user_id']);
        return Result::success($data);
    }

    /**
     * User: Yan
     * DateTime: 2023/4/4
     * @return JsonResponse
     * 用户导出
     * @throws Exception
     */
    public function export(): JsonResponse
    {
        # 跨域处理
        header('Access-Control-Allow-Origin:*');
        $params = request()->all();
        try {
            $data = $this->usersServices->export($params);
            return Result::success($data);
        } catch (PhpSpreadsheetException $e) {
            return Result::fail($e->getMessage());
        }
    }


}
