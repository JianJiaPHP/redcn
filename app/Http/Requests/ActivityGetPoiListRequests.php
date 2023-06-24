<?php

namespace App\Http\Requests;

class ActivityGetPoiListRequests extends BaseRequest
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
            'keyword' => ['required'],
            'city'    => ['required'],
        ];
    }

    public function messages()
    {
        return [
            'keyword.required' => '关键字不能为空',
            'city.required'    => '城市不能为空',
        ];
    }
}
