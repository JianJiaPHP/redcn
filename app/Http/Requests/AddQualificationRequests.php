<?php

namespace App\Http\Requests;


class AddQualificationRequests extends BaseRequest
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
            # name 身份证名称 必传 string
            'name'                               => 'required|string',
            # first_class 一级类目 必传 int
            'first_class'                        => 'required|integer',
            # second_class 二级类目 必传 int
            'second_class'                       => 'required|integer',
            # id_img_url_z 导师身份证正面url 必传 string
            'id_img_url_z'                       => 'required|string',
            # id_img_uri_z 导师身份证反面uri 必传 string
            'id_img_uri_z'                       => 'required|string',
            # id_img_url_b 导师身份证反面url 必传 string
            'id_img_url_b'                       => 'required|string',
            # id_img_uri_b 导师身份证反面uri 必传 string
            'id_img_uri_b'                       => 'required|string',
            # id_number 身份证号 必传 string 正则验证身份证格式
            'id_number'                          => 'required|string',
            # img_uri 老师头图uri 必传 string
            'img_uri'                            => 'required|string',
            # img_url 老师头图url 必传 string
            'img_url'                            => 'required|string',
            # nickname 老师昵称 必传 string
            'nickname'                           => 'required|string',
            # introduction 老师简介 必传 string
            'introduction'                       => 'required|string',
            # qualification_info 数组 必填
            'qualification_info'                 => 'required|array',
            # qualification_info.0.type 资质类型 必填 int 只允许2 3 4
            'qualification_info.0.type'          => 'required|integer|in:2,3,4',
            # qualification_info.0.image_uri 资质图片uri 必填 string
            'qualification_info.0.image_uri'     => 'required|string',
            # qualification_info.0.image_url 资质图片url 必填 string
            'qualification_info.0.image_url'     => 'required|string',
            # qualification_info.0.validity_date 资质截至有效期 必填 string
            'qualification_info.0.validity_date' => 'required|string',
        ];
    }

    public function messages()
    {
        return [
            'name.required'                               => '身份证名称不能为空',
            'name.string'                                 => '身份证名称必须为字符串',
            'first_class.required'                        => '一级类目不能为空',
            'first_class.integer'                         => '一级类目必须为整数',
            'second_class.required'                       => '二级类目不能为空',
            'second_class.integer'                        => '二级类目必须为整数',
            'id_img_url_z.required'                       => '导师身份证正面url不能为空',
            'id_img_url_z.string'                         => '导师身份证正面url必须为字符串',
            'id_img_uri_z.required'                       => '导师身份证反面uri不能为空',
            'id_img_uri_z.string'                         => '导师身份证反面uri必须为字符串',
            'id_img_url_b.required'                       => '导师身份证反面url不能为空',
            'id_img_url_b.string'                         => '导师身份证反面url必须为字符串',
            'id_img_uri_b.required'                       => '导师身份证反面uri不能为空',
            'id_img_uri_b.string'                         => '导师身份证反面uri必须为字符串',
            'id_number.required'                          => '身份证号不能为空',
            'id_number.string'                            => '身份证号必须为字符串',
            'img_uri.required'                            => '老师头图uri不能为空',
            'img_uri.string'                              => '老师头图uri必须为字符串',
            'img_url.required'                            => '老师头图url不能为空',
            'img_url.string'                              => '老师头图url必须为字符串',
            'nickname.required'                           => '老师昵称不能为空',
            'nickname.string'                             => '老师昵称必须为字符串',
            'introduction.required'                       => '老师简介不能为空',
            'introduction.string'                         => '老师简介必须为字符串',
            'qualification_info.required'                 => '资质信息不能为空',
            'qualification_info.array'                    => '资质信息必须为数组',
            'qualification_info.0.type.required'          => '资质类型不能为空',
            'qualification_info.0.type.integer'           => '资质类型必须为整数',
            'qualification_info.0.type.in'                => '资质类型只能为2 3 4',
            'qualification_info.0.image_uri.required'     => '资质图片uri不能为空',
            'qualification_info.0.image_uri.string'       => '资质图片uri必须为字符串',
            'qualification_info.0.image_url.required'     => '资质图片url不能为空',
            'qualification_info.0.image_url.string'       => '资质图片url必须为字符串',
            'qualification_info.0.validity_date.required' => '资质截至有效期不能为空',
            'qualification_info.0.validity_date.string'   => '资质截至有效期必须为字符串',
        ];
    }
}
