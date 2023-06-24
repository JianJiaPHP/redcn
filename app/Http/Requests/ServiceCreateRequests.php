<?php

namespace App\Http\Requests;

class ServiceCreateRequests extends BaseRequest
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
            'goods_title' => ['required', 'string'],
            # 权益商品分类 只允许参数 1，2，3
            'class'       => ['required', 'integer', 'in:1,2,3'],
            #covers
            'covers'      => ['required', 'string'],
            #money 金额 只允许2位小数
            'money'       => ['required', 'regex:/^\d+(\.\d{1,2})?$/','max:10'],
            'content'     => ['required', 'string'],
            #key
            'key'         => ['required', 'string'],
//            'sort'        => ['required', 'integer'],
            'is_open'     => ['required', 'integer', 'in:1,0'],
        ];
    }

    public function messages()
    {
        return [
            'goods_title.required' => '商品名称不能为空',
            'goods_title.string'   => '商品名称必须是字符串',
            'class.required'       => '权益商品分类不能为空',
            'class.integer'        => '权益商品分类必须是整数',
            'class.in'             => '权益商品分类只允许参数 1，2，3',
            'covers.required'      => '封面图不能为空',
            'covers.string'        => '封面图必须是字符串',
            'money.required'       => '金额不能为空',
            'money.regex'          => '金额只允许2位小数',
            'money.max'            => '金额最大10位',
            'content.required'     => '内容不能为空',
            'content.string'       => '内容必须是字符串',
            'key.required'         => 'key不能为空',
            'key.string'           => 'key必须是字符串',
//            'sort.required'        => '排序不能为空',
//            'sort.integer'         => '排序必须是整数',
            'is_open.required'     => '是否开启不能为空',
            'is_open.integer'      => '是否开启必须是整数',
            'is_open.in'           => '是否开启只允许参数 1，0',
        ];
    }
}
