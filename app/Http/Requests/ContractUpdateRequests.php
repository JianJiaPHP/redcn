<?php

namespace App\Http\Requests;

class ContractUpdateRequests extends BaseRequest
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
            'contract_name' => ['required'],
            'id'            => ['required', 'integer'],
            'platform_id'   => ['required', 'integer'],
        ];
    }

    public function messages()
    {
        return [
            'contract_name.required' => '套餐名称不能为空',
            'id.required'            => 'id不能为空',
            'id.integer'             => 'id必须为整数',
            'platform_id.required'   => '认证平台id不能为空',
            'platform_id.integer'    => '认证平台id必须为整数',
        ];
    }
}
