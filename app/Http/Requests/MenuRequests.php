<?php

namespace App\Http\Requests;

class MenuRequests extends BaseRequest
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
            'position'        => [
                'required',
            ], 'parent_id'    => [
                'required',
            ], 'jump_type'    => [
                'required',
            ], 'menu_name'    => [
                'required',
            ], 'jump_ios'     => [
                'required',
            ], 'jump_android' => [
                'required',
            ],
        ];
    }
}
