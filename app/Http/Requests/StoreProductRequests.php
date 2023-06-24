<?php

namespace App\Http\Requests;

class StoreProductRequests extends BaseRequest
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
            'category_id' => ['required', 'integer'],
            'cate_id' => ['required', 'integer'],
            'product_type' => ['required'],
            'store_id' => ['required', 'integer'],
            'suit_store' => ['required'],
            'product_name' => ['required'],
            'content' => ['required'],
            'price' => ['required'],
            'ot_price' => ['required'],
            'image' => ['required'],
            'opening_at' => ['required'],
            'become_at' => ['required'],

        ];
    }

    public function messages(): array
    {
        return [
            'category_id.required' => '抖音商品品类id不允许为空',
            'category_id.integer' => '抖音商品品类id必须为整数',
            'cate_id.required' => '本地商品品类id不允许为空',
            'cate_id.integer' => '本地商品品类id必须为整数',
            'product_type.required' => '请选择商品类型',
            'store_id.required' => '商家id不允许为空',
            'store_id.integer' => '商家id必须为整数',
            'suit_store.required' => '商家下的适应门店不能为空',
            'product_name.required' => '商品名称不能为空',
            'content.required' => '商品搭配不能为空',
            'price.required' => '商品原价不能为空',
            'ot_price.required' => '商品支付价格不能为空',
            'image.required' => '请最少上传一张商品图片',
            'opening_at.required' => '请配置商品售卖日期--开始时间',
            'become_at.required' => '请配置商品售卖日期--结束时间',

        ];
    }
}
