<?php

namespace App\Http\Requests;

class MeUuidRequests extends BaseRequest
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
            'uuid' => [
                'required',
                'confirmed',
            ],
            'uuid_confirmation' => [
                'required',
            ]
        ];
    }
}
