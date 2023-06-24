<?php

namespace App\Http\Requests;

class ContractCreateRequests extends BaseRequest
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
            'contract_name' => ['required'],
            'activity_id'   => ['required', 'integer'],
            'money'         => ['required', 'numeric'],
        ];
    }

    public function messages(): array
    {
        return [
            'contract_name.required' => '套餐名称不能为空',
            'activity_id.required'   => '活动id不能为空',
            'activity_id.integer'    => '活动id必须为整数',
            'money.required'         => '套餐金额不能为空',
            'money.numeric'          => '套餐金额必须为数字',
        ];
    }
}
