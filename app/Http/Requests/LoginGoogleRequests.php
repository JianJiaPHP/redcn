<?php

namespace App\Http\Requests;

class LoginGoogleRequests extends BaseRequest
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
            'account' => ['required', 'max:20', 'regex:/^[a-zA-Z0-9_]+$/'],
            //password 为密码
            'password' => ['required','min:6'],
        ];
    }

    public function messages(): array
    {
        return [
            'account.required'    => '账号不能为空',
            'account.max'         => '账号长度不能超过20个字符',
            'account.regex'       => '账号只能包含字母、数字、下划线',
            'password.required'   => '密码不能为空',
            'password.min'        => '密码长度不能少于6个字符',
        ];
    }
}
