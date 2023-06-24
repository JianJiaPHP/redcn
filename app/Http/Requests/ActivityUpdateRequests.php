<?php

namespace App\Http\Requests;

class ActivityUpdateRequests extends BaseRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'valid_start' => ['required', 'date'],
            'valid_end'   => ['required','date'],
        ];
    }

    public function messages(): array
    {
        return [
            'valid_start.required' => '活动开始时间不能为空',
            'valid_start.date'     => '活动开始时间格式不正确',
            'valid_end.required'   => '活动结束时间不能为空',
            'valid_end.date'       => '活动结束时间格式不正确',
        ];
    }
}
