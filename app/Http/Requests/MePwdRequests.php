<?php

namespace App\Http\Requests;

class MePwdRequests extends BaseRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth('api')->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            # 旧密码 查询数据库对比密码是否正确
            'old_password'          => [
                'required',
            ],
            # 最短6位 最长20位
            'password'              => [
                'required',
                'confirmed',
                'min:6',
                'max:20'
            ],
            'password_confirmation' => [
                'required',
                'same:password',
                'min:6',
                'max:20'
            ]
        ];
    }

    public function messages()
    {
        return [
            'old_password.required'          => '旧密码必须',
            'password.required'              => '新密码必须',
            'password.confirmed'             => '两次密码不一致',
            'password.min'                   => '密码不能小于6位',
            'password.max'                   => '密码最长20位',
            'password_confirmation.required' => '确认密码必须',
            'password_confirmation.same'     => '两次密码不一致',
            'password_confirmation.min'      => '密码不能小于6位',
            'password_confirmation.max'      => '密码最长20位',
        ];
    }
}
