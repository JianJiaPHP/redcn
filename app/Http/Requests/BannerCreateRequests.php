<?php

namespace App\Http\Requests;

class BannerCreateRequests extends BaseRequest
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
            # 封面在线地址
            'cover'     => ['required', 'string', 'max:255'],
            # 分类 1:首页 2:商城 3:学堂
            'classify'  => ['required', 'in:1,2,3,4'],
            # 排序
            'sort'      => ['required', 'integer', 'min:0'],
            # 是否隐藏 只能选择0 或者 1
            'is_hidden' => ['required', 'integer', 'min:0', 'max:1'],
            # 链接
            'link'      => ['nullable', 'string', 'max:255'],
            # 开始时间
            'start_time'=> ['required'],
            # 结束时间
            'end_time'  => ['required'],
        ];
    }

    public function messages(): array
    {
        return [
            'cover.required'     => '封面不能为空',
            'cover.string'       => '封面必须为字符串',
            'cover.max'          => '封面长度地址不能超过255个字符',
            'classify.required'  => '分类不能为空',
            'classify.min'       => '分类最小值为0',
            'classify.max'       => '分类最大值为1',
            'sort.required'      => '排序不能为空',
            'sort.integer'       => '排序必须为整数',
            'sort.min'           => '排序最小值为0',
            'is_hidden.required' => '是否隐藏不能为空',
            'is_hidden.integer'  => '是否隐藏必须为整数',
            'is_hidden.min'      => '是否隐藏最小值为0',
            'is_hidden.max'      => '是否隐藏最大值为1',
            'link.string'        => '链接必须为字符串',
            'link.max'           => '链接长度不能超过255个字符',
        ];
    }
}
