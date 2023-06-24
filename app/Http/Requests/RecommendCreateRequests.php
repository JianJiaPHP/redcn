<?php

namespace App\Http\Requests;

class RecommendCreateRequests extends BaseRequest
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
            # is_open
            'is_open'  => ['required', 'integer', 'in:-1,1'],
            'value'    => ['required', 'json'],
            # 1:首页人气热搜 2:首页导师推荐 3:首页达人推荐 4:首页店铺推荐 5：学堂抖平台导师 6:学堂星选达人
            'position' => ['required', 'integer', 'in:1,2,3,4,5,6'],
            # user_id
            'user_id'  => ['required'],
        ];
    }

    public function messages(): array
    {
        return [
            'is_open.required'  => 'is_open必须',
            'is_open.integer'   => 'is_open必须是整数',
            'is_open.in'        => 'is_open必须是-1或1',
            'value.required'    => 'value必须',
            'value.json'        => 'value必须是json格式',
            'position.required' => 'position必须',
            'position.integer'  => 'position必须是整数',
            'position.in'       => 'position必须是1,2,3,4,5,6',
            'user_id.required'  => 'user_id必须',
        ];
    }
}
