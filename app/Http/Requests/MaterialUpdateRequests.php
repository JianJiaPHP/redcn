<?php

namespace App\Http\Requests;

class MaterialUpdateRequests extends BaseRequest
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
//            'title'     => ['required'],
            'class_id'  => ['required'],
            'status'    => ['required'],
//            'cover_img' => ['required'],

        ];
    }

    public function messages()
    {
        return [
//            'title.required'     => 'The :attribute title :required',
            'class_id.required'  => 'The :attribute class_id be required.',
            'status.required'    => 'The :attribute status be required.',
//            'cover_img.required' => 'The :attribute cover_img be required.',
        ];
    }
}
