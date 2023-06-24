<?php


namespace App\Http\Requests;


use App\Utils\Result;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class BaseRequest extends FormRequest
{
    /**
     * 重写422返回
     * @param Validator $validator
     * @author Aii
     * @date 2020/1/16 上午11:40
     */
    public function failedValidation(Validator $validator)
    {
        throw (new HttpResponseException(Result::validateFailed($validator->errors()->first())));
    }

}
