<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Board;
use App\Models\UserInfo;
use App\Models\Users;
use App\Utils\Result;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    # 我的团队
    public function myBelow(): JsonResponse
    {
        # 获取用户信息
        $user = auth('api')->user();
        # 获取我的下级
        $userData = Users::query()->where('p_id', $user['id'])
            ->select(['id', 'nickname', 'avatar', 'register_date', 'created_at'])
            ->paginate(request()->query('limit', 15));
        return Result::success($userData);
    }

    # 获取实名信息
    public function getRealName(): JsonResponse
    {
        $userId = auth('api')->id();
        $data = Users::query()->where('id',$userId)->select(['is_real_name','real_name','real_card'])->first();
        if (empty($data)){
            return Result::fail("用户不存在");
        }
        return Result::success($data);
    }
    # 设置实名信息
    public function addRealName(): JsonResponse
    {
        $params = request()->all();
        # 验证
        $validator = validator($params, [
            'real_name' => 'required',
            'real_card' => 'required',
        ], [
            'real_name.required' => '真实姓名不能为空',
            'real_card.required' => '身份证号不能为空',
        ]);
        if ($validator->fails()) {
            return Result::fail($validator->errors()->first());
        }
        $userId = auth('api')->id();
        $user = Users::query()->where('id',$userId)->exists();
        if (!$user){
            return Result::fail("用户不存在");
        }
        $data = [
            'is_real_name' => 1,
            'real_name' => $params['real_name'],
            'real_card' => $params['real_card'],
        ];
        $res = Users::query()->where('id',$userId)->update($data);
        if (!$res){
            return Result::fail("设置失败");
        }
        return Result::success("设置成功");
    }

    # 设置留言
    public function setBoard(): JsonResponse
    {
        $params = request()->all();
        # 验证
        $validator = validator($params, [
            'text' => 'required',
        ], [
            'real_name.required' => '留言板不能为空',
        ]);
        if ($validator->fails()) {
            return Result::fail($validator->errors()->first());
        }
        $userId = auth('api')->id();
        $res = Board::query()->create(['text'=>$params['text'],'user_id'=>$userId]);
        if (!$res){
            return Result::fail("留言失败");
        }
        return Result::success("留言成功");
    }

    # 获取中国梦个人信息
    public function getDream(): JsonResponse
    {
        $userId = auth('api')->id();
        $data = UserInfo::query()->where('user_id',$userId)->first();
        if (empty($data)){
            return Result::success(
                [
                    'name'=>'',
                    'date'=>'',
                    'phone'=>'',
                    'address'=>'',
                ]
            );
        }
        return Result::success($data);
    }
    # 设置中国梦个人信息
    public function setDream(): JsonResponse
    {
        $params = request()->all();
        # 验证
        $validator = validator($params, [
            'name' => 'required',
            'date' => 'required',
            'phone' => 'required',
            'address' => 'required',
        ], [
            'name.required' => '姓名不能为空',
            'date.required' => '日期不能为空',
            'phone.required' => '手机号不能为空',
            'address.required' => '地址不能为空',
        ]);
        if ($validator->fails()) {
            return Result::fail($validator->errors()->first());
        }
        $userId = auth('api')->id();
        $user = UserInfo::query()->where('user_id',$userId)->exists();
        if ($user){
            $data = [
                'name' => $params['name'],
                'date' => $params['date'],
                'phone' => $params['phone'],
                'address' => $params['address'],
            ];
            $res = UserInfo::query()->where('user_id',$userId)->update($data);
            if (!$res){
                return Result::fail("设置失败");
            }
            return Result::success("设置成功");
        }
        $data = [
            'user_id' => $userId,
            'name' => $params['name'],
            'date' => $params['date'],
            'phone' => $params['phone'],
            'address' => $params['address'],
        ];
        $res = UserInfo::query()->create($data);
        if (!$res){
            return Result::fail("设置失败");
        }
        return Result::success("设置成功");
    }

    # 生成提现订单号 不能重复
    public function getWithdrawalNo(): string
    {
        $withdrawalNo = 'TX' . date('YmdHis') . rand(100000, 999999);
        $count = Withdrawal::query()->where('withdrawal_no', $withdrawalNo)->count();
        if ($count > 0) {
            $this->getWithdrawalNo();
        }
        return $withdrawalNo;
    }
}
