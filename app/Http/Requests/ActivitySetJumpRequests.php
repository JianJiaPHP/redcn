<?php

namespace App\Http\Requests;


class ActivitySetJumpRequests extends BaseRequest
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
            'poi_id'   => ['required'],
            'poi_name' => ['required'],
            'province' => ['required'],
        ];
    }

    public function messages()
    {
        return [
            'poi_id.required'   => 'poi_id不能为空',
            'poi_name.required' => 'poi_name不能为空',
            'province.required' => 'province不能为空',
        ];
    }
}
