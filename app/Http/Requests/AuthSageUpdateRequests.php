<?php

namespace App\Http\Requests;

class AuthSageUpdateRequests extends BaseRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
//            'platform_levels' => ['required'],
            'id'              => ['required','integer'],
            'status'          => ['required', 'integer', 'in:1,-1'],
        ];
    }

    public function messages()
    {
        return [
//            'platform_levels.required' => '认证标签|达人等级未选择',
            'id.required'              => '认证平台id不能为空',
            'id.integer'               => '认证平台id必须为整数',
            'status.required'          => '认证平台审核状态不能为空',
            'status.integer'           => '认证平台审核状态必须为整数',
            'status.in'                => '认证平台审核状态必须为驳回或同意',
        ];
    }
}
