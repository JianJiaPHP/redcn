<?php

namespace App\Http\Requests;

class MeUpdateRequests extends BaseRequest
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
            'account' => ['required', 'max:20'],
            'nickname' => ['required', 'max:20'],
        ];
    }
}
