<?php

namespace App\Http\Requests;

class CommodityRequests extends BaseRequest
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
            #商品昵称
            'product_name'                  => ['required'],
            #供应商店铺id
            'store_id'                      => ['required', 'integer'],
            #商品分类
            'cate_id'                       => ['required', 'integer'],
            #商品封面图
            'image'                         => ['required', 'string'],
            #商品轮播图
            'slider_image'                  => ['required', 'string'],
            #商品规格
            'spec_type'                     => ['required', 'integer'],
            #运费模板
            'temp_id'                       => ['required', 'integer'],
            #商品类型
//            'type'                          => ['required', 'integer'],
            #商品属性组合
            'attribute_arr'                 => ['required', 'string'],
//            #商品昵称
//            'title'                         => ['required', 'string'],


        ];
    }

    public function messages(): array
    {
        return [
            'product_name.required'             => '商品名称不能为空',
            'store_id.required'                 => '供应商店铺不能为空',
            'store_id.integer'                  => '供应商店铺必须为整数',
            'cate_id.required'                  => '商品分类不能为空',
            'cate_id.integer'                   => '商品分类必须为整数',
            'image.required'                    => '商品封面图不能为空',
            'image.string'                      => '商品封面图必须为字符串',
            'slider_image.required'             => '商品轮播图不能为空',
            'slider_image.string'               => '商品轮播图必须为字符串',
            'spec_type.required'                => '商品规格不能为空',
            'spec_type.integer'                 => '商品规格必须为整数',
            'temp_id.required'                  => '运费模板不能为空',
            'temp_id.integer'                   => '运费模板必须为整数',
            'type.required'                     => '商品类型不能为空',
            'type.integer'                      => '商品类型必须为整数',
            'attribute_arr.required'            => '商品属性不能为空',
            'attribute_arr.string'              => '商品属性必须为字符串',
        ];
    }
}
