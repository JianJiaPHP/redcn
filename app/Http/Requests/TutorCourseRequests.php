<?php

namespace App\Http\Requests;

class TutorCourseRequests extends BaseRequest
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
            #课程名称
            'goods_title'                  => ['required'],
            #供应导师id
            'tutors_id'                    => ['required', 'integer'],
            #课程链接
            'course_link'                  => ['required', 'string'],
            #课程提取码
            'course_code'                  => ['required', 'string'],
            #商品封面图
            'covers'                         => ['required', 'string'],
            #商品轮播图
            'slider_image'                  => ['required', 'string'],
            'content'                  => ['required', 'string'],
            #课程售价
//            'money'                       => ['required'],
            #课程成本价
//            'cost_price'                       => ['required'],
            #课程利润价
//            'cost'                       => ['required'],
            #课程库存
            'stock'                       => ['required', 'integer'],

        ];
    }

    public function messages(): array
    {
        return [
            'goods_title.required'                  => '课程名称不能为空',
            'tutors_id.required'                    => '供应导师不能为空',
            'tutors_id.integer'                     => '供应导师必须为整数',
            'course_link.required'                    => '课程链接不能为空',
            'course_link.string'                      => '课程链接必须为字符串',
            'course_code.required'                    => '课程提取码不能为空',
            'course_code.string'                      => '课程提取码必须为字符串',
            'covers.required'                    => '课程商品封面图不能为空',
            'covers.string'                      => '课程商品封面图必须为字符串',
            'slider_image.required'             => '课程商品轮播图不能为空',
            'slider_image.string'               => '课程商品轮播图必须为字符串',
            'money.required'                  => '商品售价不能为空',
            'cost_price.required'                  => '商品成本价不能为空',
            'cost.required'                  => '商品利润价不能为空',
            'stock.required'                  => '商品库存不能为空',
            'stock.integer'                   => '商品库存必须为整数',
            'content.required'                    => '课程商品详情图集不能为空',
            'content.string'                      => '课程商品详情图必须为字符串',
        ];
    }
}
