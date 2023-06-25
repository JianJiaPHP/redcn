<?php

namespace App\Http\Controllers\Api;

# 产品类
use App\Models\Goods;
use App\Utils\Result;
use Illuminate\Http\JsonResponse;

class GoodsController
{

    # 指定产品列表
    public function list(): JsonResponse
    {
        $params = request()->all();
        # 验证
        $validator = validator($params, [
            'type' => 'required',
        ], [
            'type.required' => '产品类型不能为空',
        ]);
        if ($validator->fails()) {
            return Result::fail($validator->errors()->first());
        }
        $list = Goods::query()->where('type', $params['type'])->get();
        foreach ($list as $key => $value) {
            $list[$key]['totalAmount'] = bcadd(bcmul($value['income'], $value['validity_day'], 2), $value['end_rewards']);
        }
        return Result::success($list);
    }
}
