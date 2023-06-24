<?php

namespace App\Http\Requests;

class TopicRequests extends BaseRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth('api')->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'activity_id' => [
                'required',
            ],
        ];
    }

    public function messages()
    {
        return [
            'activity_id.required' => '活动id不能为空',
        ];
    }
}
