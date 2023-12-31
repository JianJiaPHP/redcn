<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Board;
use App\Models\UserBankCards;
use App\Models\UserInfo;
use App\Models\Users;
use App\Utils\Result;
use Illuminate\Http\JsonResponse;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class UserController extends Controller
{
    # 我的团队
    public function myBelow(): JsonResponse
    {
        # 获取用户信息
        $user = auth('api')->user();
        # 我的所有下级
        $userPid = Users::getMySubordinateUserId($user['id']);
        # 一共多少下级
        $totalPid = count($userPid);
        # 总收益
        $totalIncome = Users::getMySubordinateTotalIncome($user['id']);
        # $totalIncome 如果是负数转正数
        return Result::success(
            [
                'totalPid'    => $totalPid,#下级总数
                # 转正数
                'totalIncome' => $totalIncome,# 总收益
            ]
        );
    }

    # 一级团队
    public function myBelowOne(): JsonResponse
    {
        $user = auth('api')->user();
        # 我的一级
        $userPidOne = Users::getMySubordinateUserId($user['id'], 1);
        # 一级人数
        $totalPidOne = count($userPidOne);
        # 一级列表
        $userPidOneList = Users::query()->whereIn('id', $userPidOne)
            ->select(['id', 'nickname','phone', 'avatar', 'register_date', 'created_at'])
            ->paginate(request()->query('limit', 15));
        foreach ($userPidOneList as &$value) {
            $value['total_income'] = Users::getUserIdIncome($value['id']);
        }
        # 一级总收益
        $totalIncomeOne = Users::getMySubordinateTotalIncome($user['id']);
        return Result::success(
            [
                'userPidOneList' => $userPidOneList,#一级列表
                'totalIncomeOne' => $totalIncomeOne,#一级总收益
                'totalPidOne'    => $totalPidOne,#一级人数
            ]
        );
    }

    # 二级团队
    public function myBelowTwo(): JsonResponse
    {
        $user = auth('api')->user();
        # 我的二级
        $userPidTwo = Users::getMySubordinateUserId($user['id'], 2);
        # 二级人数
        $totalPidTwo = count($userPidTwo);
        # 二级总收益
        $totalIncomeTwo = Users::getMySubordinateTotalIncome($user['id']);
        # 二级列表
        $userPidTwoList = Users::query()->whereIn('id', $userPidTwo)
            ->select(['id', 'nickname','phone', 'avatar', 'register_date', 'created_at'])
            ->paginate(request()->query('limit', 15));
        foreach ($userPidTwoList as &$value1) {
            $value1['total_income'] = Users::getUserIdIncome($value1['id']);
        }
        return Result::success(
            [
                'userPidTwoList' => $userPidTwoList,#二级列表
                'totalIncomeTwo' => $totalIncomeTwo,#二级总收益
                'totalPidTwo'    => $totalPidTwo,#二级人数
            ]
        );
    }


    # 获取实名信息
    public function getRealName(): JsonResponse
    {
        $userId = auth('api')->id();
        $data = Users::query()->where('id', $userId)->select(['is_real_name', 'real_name', 'real_card'])->first();
        if (empty($data)) {
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
        $user = Users::query()->where('id', $userId)->exists();
        if (!$user) {
            return Result::fail("用户不存在");
        }
        $data = [
            'is_real_name' => 1,
            'real_name'    => $params['real_name'],
            'real_card'    => $params['real_card'],
        ];
        $res = Users::query()->where('id', $userId)->update($data);
        if (!$res) {
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
            'text.required' => '留言板不能为空',
        ]);
        if ($validator->fails()) {
            return Result::fail($validator->errors()->first());
        }
        $userId = auth('api')->id();
        $res = Board::query()->updateOrCreate(['user_id' => $userId],['text' => $params['text']]);
        if (!$res) {
            return Result::fail("留言失败");
        }
        return Result::success("留言成功");
    }

    # 获取我的留言
    public function getBoard(): JsonResponse
    {
        $userId = auth('api')->id();
        $data = Board::query()->where('user_id', $userId)->value('text');
        if (empty($data)) {
            return Result::success([]);
        }
        return Result::success($data);
    }

    # 获取中国梦个人信息
    public function getDream(): JsonResponse
    {
        $userId = auth('api')->id();
        $data = UserInfo::query()->where('user_id', $userId)->first();
        if (empty($data)) {
            return Result::success(
                [
                    'name'    => '',
                    'date'    => '',
                    'phone'   => '',
                    'address' => '',
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
            'name'    => 'required',
            'date'    => 'required',
            'phone'   => 'required',
            'address' => 'required',
        ], [
            'name.required'    => '姓名不能为空',
            'date.required'    => '日期不能为空',
            'phone.required'   => '手机号不能为空',
            'address.required' => '地址不能为空',
        ]);
        if ($validator->fails()) {
            return Result::fail($validator->errors()->first());
        }
        $userId = auth('api')->id();
        $user = UserInfo::query()->where('user_id', $userId)->exists();
        if ($user) {
            $data = [
                'name'    => $params['name'],
                'date'    => $params['date'],
                'phone'   => $params['phone'],
                'address' => $params['address'],
            ];
            $res = UserInfo::query()->where('user_id', $userId)->update($data);
            if (!$res) {
                return Result::fail("设置失败");
            }
            return Result::success("设置成功");
        }
        $data = [
            'user_id' => $userId,
            'name'    => $params['name'],
            'date'    => $params['date'],
            'phone'   => $params['phone'],
            'address' => $params['address'],
        ];
        $res = UserInfo::query()->create($data);
        if (!$res) {
            return Result::fail("设置失败");
        }
        return Result::success("设置成功");
    }

    # 用户银行卡信息
    public function getBankCard(): JsonResponse
    {
        $userId = auth('api')->id();
        $data = UserBankCards::query()->where('user_id', $userId)->get();
        return Result::success($data);
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

    # 获取邀请二维码
    public function share(){
        $userId = auth('api')->id();
        $code = Users::query()->where('id',$userId)->value('invitation');
        $img =  QrCode::format('png')->size(200)->generate(env('H5_URL').'#/pages/login/register?invitation='.$code);    //format 是指定生成文件格式  默认格式是svg,可以直接在浏览器打开，png不能直接显示
        return Result::success('data:image/png;base64,' . base64_encode($img ));

    }
}
