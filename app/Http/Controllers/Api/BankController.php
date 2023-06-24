<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserBankCards;
use App\Models\Users;
use App\Utils\Result;
use Illuminate\Http\JsonResponse;

class BankController extends Controller
{
    # 我的银行卡列表
    public function myBank(): JsonResponse
    {
        $userId = auth('api')->id();
        $data = UserBankCards::query()->where('user_id', $userId)->orderByDesc('id')->get();
        return Result::success($data);
    }

    # 添加银行卡
    public function addBank(): JsonResponse
    {
        $userId = auth('api')->id();
        $params = request()->all();
        #验证器
        $validator = validator($params, [
            'bank_name' => 'required',
            'card_number' => 'required',
            'cardholder_name' => 'required',
        ], [
            'bank_name.required' => '银行名称不能为空',
            'card_number.required' => '银行卡号不能为空',
            'cardholder_name.required' => '持卡人姓名不能为空',
        ]);
        if ($validator->fails()) {
            return Result::fail($validator->errors()->first());
        }
        $user = Users::query()->where('id', $userId)->exists();
        if (!$user) {
            return Result::fail("用户不存在");
        }
        # 银行卡最多绑定8张
        $count = UserBankCards::query()->where('user_id', $userId)->count();
        if ($count >= 8) {
            return Result::fail("银行卡最多绑定8张");
        }
        $data = [
            'user_id' => $userId,
            'bank_name' => $params['bank_name'],
            'card_number' => $params['card_number'],
            'cardholder_name' => $params['cardholder_name'],
        ];
        $res = UserBankCards::query()->create($data);
        if (!$res) {
            return Result::fail("添加失败");
        }
        return Result::success("添加成功");
    }

    # 解绑银行卡
    public function delBank(): JsonResponse
    {
        $userId = auth('api')->id();
        $params = request()->all();
        #验证器
        $validator = validator($params, [
            'id' => 'required',
        ], [
            'id.required' => 'id不能为空',
        ]);
        if ($validator->fails()) {
            return Result::fail($validator->errors()->first());
        }
        $user = Users::query()->where('id', $userId)->exists();
        if (!$user) {
            return Result::fail("用户不存在");
        }
        $res = UserBankCards::query()->where(['id' => $params['id'], 'user_id' => $userId])->delete();
        if (!$res) {
            return Result::fail("解绑失败");
        }
        return Result::success("解绑成功");
    }
}
