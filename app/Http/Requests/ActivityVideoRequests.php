<?php

namespace App\Http\Requests;

use App\Http\Requests\BaseRequest;

class ActivityVideoRequests extends BaseRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return auth('api')->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
           'url' => [
                'required',
            ],
//           'activity_id' => [
//                'required',
//                'integer'
//            ]
        ];
    }

    public function messages(): array
    {
        return [
            'url.required' => 'url必须',
//            'activity_id.required' => 'activity_id必须',
//            'activity_id.integer' => 'activity_id必须是整数',
        ];
    }
}
