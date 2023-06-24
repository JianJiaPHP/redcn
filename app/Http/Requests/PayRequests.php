<?php

namespace App\Http\Requests;

class PayRequests extends BaseRequest
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
            'total_amount' => ['required'],
            'subject'      => ['required'],
            'meal_type'    => ['required'],
//            'store_id'     => ['required'],
        ];
    }

    public function messages()
    {
        return [
            'total_amount.required' => '金额不能为空',
            'subject.required'      => '商品名称不能为空',
            'meal_type.required'    => '套餐类型不能为空',
//            'store_id.required'     => '商家ID不能为空',
        ];
    }
}
