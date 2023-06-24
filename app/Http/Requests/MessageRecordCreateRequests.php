<?php

namespace App\Http\Requests;

class MessageRecordCreateRequests extends BaseRequest
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
    public function rules(): array
    {
        return [
            # type类型 int类型 参数只允许1,2,3,4 必填
            'type'    => ['required', 'integer', 'in:1,2,3,4'],
            // 标题 必填
            'title'   => ['required', 'string'],
            // 内容 必填
            'content' => ['required', 'string'],
            // 发送对象 必填
            'users'   => ['required', 'string'],
            // 图片
            'image'   => ['nullable', 'string'],
            // 富文本
            'text'    => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'type.required'    => 'type类型必填',
            'type.integer'     => 'type类型必须为整数',
            'type.in'          => 'type类型只允许1,2,3,4',
            'title.required'   => '标题必填',
            'title.string'     => '标题必须为字符串',
            'content.required' => '内容必填',
            'content.string'   => '内容必须为字符串',
            'users.required'   => '发送对象必填',
            'users.string'     => '发送对象必须为字符串',
            'image.string'     => '图片必须为字符串',
            'text.string'      => '富文本必须为字符串',
        ];
    }
}
