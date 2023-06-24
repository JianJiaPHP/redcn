<?php

namespace App\Http\Requests;

class ActivityStoreRequests extends BaseRequest
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
            'name'                     => ['required', 'string', 'max:255'],
            'introduce'                => ['required'],
            'store_id'                 => ['required', 'integer'],
            'cover'                    => ['required', 'string'],
            'details_banner'           => ['required'],
            'valid_start'              => ['required', 'date'],
            'valid_end'                => ['required', 'date'],
            'number'                   => ['required', 'integer', 'min:1'],
            # 转发佣金金额
            'commission_forward_money' => ['required', 'numeric', 'regex:/^\d+(\.\d{1,2})?$/'],
            # 富文本
            'rule'                     => ['required'],
        ];
    }

    public function messages()
    {
        return [
            'name.required'                     => '活动名称不能为空',
            'introduce.required'                => '活动介绍不能为空',
            'store_id.required'                 => '商户id不能为空',
            'store_id.integer'                  => '商户id必须为整数',
            'cover.required'                    => '活动封面不能为空',
            'cover.string'                      => '活动封面必须为字符串',
            'details_banner.required'           => '活动详情banner不能为空',
            'valid_start.required'              => '活动开始时间不能为空',
            'valid_start.date'                  => '活动开始时间格式不正确',
            'valid_end.required'                => '活动结束时间不能为空',
            'valid_end.date'                    => '活动结束时间格式不正确',
            'number.required'                   => '活动参与人数不能为空',
            'number.integer'                    => '活动参与人数必须为整数',
            'number.min'                        => '活动参与人数不能小于1',
            'commission_forward_money.required' => '转发佣金金额不能为空',
            'commission_forward_money.numeric'  => '转发佣金金额必须为数字',
            'commission_forward_money.regex'    => '转发佣金金额格式不正确',
            'rule.required'                     => '活动规则不能为空',
        ];
    }
}
