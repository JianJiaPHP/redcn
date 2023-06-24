<?php

namespace App\Http\Requests;

class LoginRequests extends BaseRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            //account 为账号 限制长度 不能有特殊符号
            'account'     => ['required', 'max:20', 'regex:/^[a-zA-Z0-9_]+$/'],
            //password 为密码
            'password'    => ['required', 'min:6'],
            //google_code 为谷歌验证码
            'google_code' => [ 'min:6'],
            //secret 可以不传参
            'secret'      => ['nullable'],
        ];
    }

    public function messages(): array
    {
        return [
            'account.required'     => '账号不能为空',
            'account.max'          => '账号长度不能超过20个字符',
            'account.regex'        => '账号只能包含字母、数字、下划线',
            'password.required'    => '密码不能为空',
            'password.min'         => '密码长度不能少于6个字符',
            'google_code.required' => '谷歌验证码不能为空',
            'google_code.min'      => '谷歌验证码长度不能少于6个字符',

        ];
    }
}
