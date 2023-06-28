<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Models\AccumulateConfig;
use App\Models\Config;
use App\Models\Users;
use App\Services\Api\UserAccountBonusService;
use App\Utils\Result;
use Carbon\Carbon;
use DB;
use Exception;
use Hash;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Redis;

use Validator;

class AuthController extends Controller
{
    # 生成图形验证码
    public function captcha(): JsonResponse
    {
        $codeImg = app('captcha')->create('default', true);
        return Result::success(['captcha' => $codeImg]);
    }
    # 验证交易密码是否正确
    public function checkPayPassword(): JsonResponse
    {
        $params = request()->all();
        $validator = Validator::make($params, [
            'pay_password' => 'required',
        ], [
            'pay_password.required' => '交易密码必填',
        ]);
        if ($validator->fails()) {
            return Result::fail($validator->errors()->first());
        }
        $user = auth('api')->user();
        if (!Hash::check(md5($params['pay_password']), $user->pay_password)) {
            return Result::fail('交易密码错误');
        }
        return Result::success();
    }
    /**
     * 修改密码
     * @throws ApiException
     */
    public function changePassword(): JsonResponse
    {
        $params = request()->all();
        if (empty($params['key'])) {
            throw new ApiException("验证码key必填");
        }
        $validator = Validator::make($params, [
            'account'    => 'required|regex:/^1[3456789][0-9]{9}$/',
            'code'       => 'required|captcha_api:' . $params['key'],
            'password'   => 'required|between:6,20',
            'repassword' => 'required|same:password',
            'key'        => 'required',
        ], [
            'key.required'        => '验证码key必填',
            'account.required'    => '手机号必填',
            'account.regex'       => '手机号格式错误',
            'code.required'       => '验证码必填',
            'code.captcha_api'    => '验证码错误,请重新输入',
            'password.required'   => '密码必填',
            'password.between'    => '密码长度必须为6-20位',
            'repassword.required' => '确认密码必填',
            'repassword.same'     => '两次密码不一致',
        ]);
        if ($validator->fails()) {
            return Result::fail($validator->errors()->first());
        }
        $user = Users::query()->where('phone', $params['account'])->first();
        if (!$user) {
            throw new ApiException("账号不存在");
        }
        $user->password = Hash::make(md5($params['password']));
        $user->save();
        return Result::success();
    }

    /**
     * 修改支付密码
     * @throws ApiException
     */
    public function changePayPassword(): JsonResponse
    {
        $params = request()->all();
        if (empty($params['key'])) {
            throw new ApiException("验证码key必填");
        }
        $validator = Validator::make($params, [
            'account'    => 'required|regex:/^1[3456789][0-9]{9}$/',
            'password'   => 'required|between:6,20',
            'repassword' => 'required|same:password',
            'code'       => 'required|captcha_api:' . $params['key'],
            'key'        => 'required',
        ], [
            'key.required'        => '验证码key必填',
            'account.required'    => '手机号必填',
            'account.regex'       => '手机号格式错误',
            'code.required'       => '验证码必填',
            'code.captcha_api'    => '验证码错误,请重新输入',
            'password.required'   => '密码必填',
            'password.between'    => '密码长度必须为6-20位',
            'repassword.required' => '确认密码必填',
            'repassword.same'     => '两次密码不一致',
        ]);
        if ($validator->fails()) {
            return Result::fail($validator->errors()->first());
        }
        $user = Users::query()->where('phone', $params['account'])->first();
        if (!$user) {
            throw new ApiException("账号不存在");
        }
        $user->pay_password = Hash::make(md5($params['password']));
        $user->save();
        return Result::success();
    }

    /**
     * User: Yan
     * DateTime: 2023/5/8
     * @return JsonResponse
     * 账号密码注册
     * @throws ApiException
     */
    public function register(): JsonResponse
    {
        #验证器
        $params = request()->all();
        if (empty($params['key'])){
            throw new ApiException("验证码key必填");
        }
        $validator = Validator::make($params, [
            'account'      => 'required|regex:/^1[3456789][0-9]{9}$/',
            'password'     => 'required|min:6|max:30',
            'pay_password' => 'required|min:6|max:30',
            'code'         => 'required|captcha_api:' . $params['key'],
            'key'          => 'required',
        ], [
            'account.required'      => '手机号必填',
            'account.regex'         => '手机号格式错误',
            'password.required'     => '密码必填',
            'password.min'          => '密码最少6位',
            'password.max'          => '密码最多30位',
            'pay_password.required' => '支付密码必填',
            'pay_password.min'      => '支付密码最少6位',
            'pay_password.max'      => '支付密码最多30位',
            'key.required'          => '验证码key必填',
            'code.required'         => '验证码必填',
            'code.captcha_api'      => '验证码错误,请重新输入',
        ]);
        if ($validator->fails()) {
            return Result::fail($validator->errors()->first());
        }
        #验证验证码
        try {
            #验证账号是否存在
            $user = Users::query()->where('phone', $params['account'])->first();
            if ($user) {
                throw new ApiException("账号已存在");
            }
            #创建账号
            $user = $this->createUsers($params);
            if (!$user) {
                throw new ApiException("注册失败");
            }
            $token = auth('api')->login($user);
            if (!$token) {
                throw new ApiException("登录失败！");
            }
            return Result::success([
                'access_token' => $token,
                'token_type'   => 'bearer',
                'expires_in'   => auth('api')->factory()->getTTL() * 60
            ]);
        } catch (Exception $exception) {
            throw new ApiException($exception->getMessage());
        }


    }

    /**
     * @param array $params
     * @return Builder|Model
     * @throws ApiException
     */
    public function createUsers(array $params)
    {
        $invitation = 0;
        if (!empty($params['invitation'])) {
            $invitation = Users::query()->where('invitation', $params['invitation'])->value('id') ?? 0;
        }
        try {
            $password = $params['password'] ?? null;
            $password = $password ? Hash::make(md5($params['password'])) : $password;

            $pay_password = $params['pay_password'] ?? null;
            $pay_password = $pay_password ? Hash::make(md5($params['pay_password'])) : $password;

            DB::beginTransaction();
            $user = Users::query()->create([
                'phone'           => $params['account'],
                'nickname'        => $params['nickname'] ?? $params['account'],
                # 默认头像 img/head.png
                'avatar'          => 'img/head.png',
                'password'        => $password,
                'pay_password'    => $pay_password,
                'register_date'   => Carbon::now(),
                'last_login_date' => Carbon::now(),
                'last_login_ip'   => request()->getClientIp(),
                # 随机生成6位数邀请码
                'invitation'      => $this->createInvitation(),
                'p_id'            => $invitation ?? 0,
            ]);
            if (!$user) {
                DB::rollBack();
                throw new ApiException("注册失败！");
            }
            # 上级邀请奖励
            if ($invitation > 0) {
                // 尝试获取锁
                $lockAcquired = Redis::set("invitation_award_" . $user['id'], 1, 'EX', 10, 'NX');
                if ($lockAcquired){
                    UserAccountBonusService::userAccount($invitation, (new Config())->getAll()['invitation.award'], '邀请新用户奖励', 1, $user['id']);
                }
                # 解锁
                Redis::del("invitation_award_" . $user['id']);
            }
            # 注册成功赠送奖励金6000元
            UserAccountBonusService::userAccount($user['id'], (new Config())->getAll()['invitation.value'], '注册成功赠送奖励金', 2);
            DB::commit();
            return $user;
        } catch (Exception $e) {
            throw new ApiException($e->getMessage());
        }

    }

    public function createInvitation()
    {
        #6位随机字符串邀请码
        $invitation = $this->str_random(6);
        $user = Users::query()->where('invitation', $invitation)->first();
        if ($user) {
            $this->createInvitation();
        }
        return $invitation;
    }

    public function str_random($length = 6)
    {
        $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        return substr(str_shuffle(str_repeat($pool, $length)), 0, $length);
    }

    /**
     * 账号密码
     * User: Yan
     * DateTime: 2023/5/8
     * @return JsonResponse
     * @throws ApiException
     */
    public function login(): JsonResponse
    {
        $params = request()->all();
        $validator = Validator::make($params, [
            'account'  => 'required|regex:/^1[3456789][0-9]{9}$/',
            'password' => 'required|min:6|max:30',
        ], [
            'account.required'  => '手机号必填',
            'account.regex'     => '手机号格式错误',
            'password.required' => '密码必填',
            'password.min'      => '密码最少6位',
            'password.max'      => '密码最多30位',
        ]);
        if ($validator->fails()) {
            return Result::fail($validator->errors()->first());
        }
        $user = Users::query()->where('phone', $params['account'])->first();

        if (!$user) {
            throw new ApiException("账号不存在");
        }
        if ($user->is_enabled == 2) {
            throw new ApiException("账号已被禁用");
        }
        if (!$user->password) {
            throw new ApiException("账号类型错误,账号未创建密码");
        }
        if (!Hash::check(md5($params['password']), $user->password)) {
            throw new ApiException("密码错误");
        }
        $token = auth('api')->login($user);
        if (!$token) {
            throw new ApiException("登录失败！");
        }
        $user->last_login_date = Carbon::now()->toDateTimeString();
        $user->last_login_ip = request()->getClientIp();
        $user->save();
        return Result::success([
            'access_token' => $token,
            'token_type'   => 'bearer',
            'expires_in'   => auth('api')->factory()->getTTL() * 60
        ]);
    }

    /**
     * Get users
     * @return JsonResponse
     */
    public function me(): JsonResponse
    {
        $userId = auth('api')->id();
        $userInfo = Users::query()->where('id', $userId)->first();
        #修改is_login
        return Result::success($userInfo);
    }

    /**
     * User: Yan
     * DateTime: 2023/5/18
     * @return JsonResponse
     * @throws ApiException
     */
    public function update(): JsonResponse
    {
        $params = request()->all();

        $userId = auth('api')->id();
        $userInfo = Users::query()->where('id', $userId)->first();
        if (!$userInfo) {
            throw new ApiException("用户不存在");
        }
        $update = [];
        if (!empty($params['avatar'])) {
            $validator = Validator::make($params, [
                #头像 必须是jpg jpeg png格式
                'avatar' => 'required|string',
            ], [
                'avatar.required' => '头像必填',
                'avatar.string'   => '头像必须是字符串',
            ]);
            if ($validator->fails()) {
                return Result::fail($validator->errors()->first());
            }
            $update['avatar'] = $params['avatar'];
        }
        if (!empty($params['nickname'])) {
            $validator = Validator::make($params, [
                #昵称
                'nickname' => 'required|string',

            ], [
                'nickname.required' => '昵称必填',
                'nickname.string'   => '昵称必须是字符串',
            ]);
            if ($validator->fails()) {
                return Result::fail($validator->errors()->first());
            }
            $update['nickname'] = $params['nickname'];
        }
        $res = Users::query()->where('id', $userId)->update($update);
        return Result::choose($res);
    }

    /**
     * Log the user out (Invalidate the token).
     * @return JsonResponse
     */
    public function logout(): JsonResponse
    {
        auth('api')->logout();
        return Result::success('Successfully logged out');
    }
}
