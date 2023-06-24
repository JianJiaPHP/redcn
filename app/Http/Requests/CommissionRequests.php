<?php

namespace App\Http\Requests;

class CommissionRequests extends BaseRequest
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
            'commission_love' => [
                'required'
            ],
            'commission_play' => [
                'required'
            ],
            'commission_comments' => [
                'required'
            ],
            'commission_forward' => [
                'required'
            ],
            'love_total' => [
                'required','integer','between:1,99999999'
            ],
            'play_total' => [
                'required','integer','between:1,99999999'
            ],
            'comments_total' => [
                'required','integer','between:1,99999999'
            ]
        ];
    }
}
