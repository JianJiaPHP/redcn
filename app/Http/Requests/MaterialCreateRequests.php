<?php

namespace App\Http\Requests;

class MaterialCreateRequests extends BaseRequest
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
//            'title'       => ['required'],
            'class_id'    => ['required'],
            'store_id'    => ['required'],
            'activity_id' => ['required'],
            'video_url'   => ['required'],
            'status'      => ['required'],
//            'cover_img'   => ['required'],

        ];
    }

    public function messages()
    {
        return [
//            'title.required'       => 'The :attribute title :required',
            'class_id.required'    => 'The :attribute class_id be required.',
            'store_id.required'    => 'The :attribute store_id be required.',
            'activity_id.required' => 'The :attribute activity_id be required.',
            'video_url.required'   => 'The :attribute video_url be required.',
            'status.required'      => 'The :attribute status be required.',
//            'cover_img.required'   => 'The :attribute cover_img be required.',
        ];
    }
}
