<?php

namespace App\Http\Controllers\Api;

# 订单管理
use App\Models\UserGoods;
use App\Models\UserGoodsLog;
use App\Utils\Result;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class OrderController
{

    # 产品购买记录
    public function payLog(): JsonResponse
    {
        $userId = auth('api')->id();
        $where['user_id'] = $userId;
        $where[] = ['end_date','>=', Carbon::now()->toDateTimeString()];
        # 持有产品列表
        $userGoodsData = UserGoods::query()
            ->where($where)
            ->orderByDesc('id')->select(['id','user_id','goods_id','status','type','name','amount','income','end_date'])->get();
        foreach ($userGoodsData as &$value) {
            # 查询昨日收益
            $value['yesterday_income'] = UserGoodsLog::query()->where('user_goods_id', $value['id'])
                ->whereDate('date', Carbon::yesterday())->sum('income')??0;
            # 查询持有收益
            $value['hold_income'] = UserGoodsLog::query()->where('user_goods_id', $value['id'])->sum('income');
            # 查询持有收益率
            $value['hold_income_rate'] = bcdiv($value['hold_income'],$value['amount'],2);
        }
        return Result::success($userGoodsData);
    }

    # 指定产品收益明细
    public function incomeLog(): JsonResponse
    {
        $params = request()->all();
        # 验证
        $validator = validator($params, [
            'user_goods_id' => 'required',
        ], [
            'user_goods_id.required' => '产品ID不能为空',
        ]);
        if ($validator->fails()) {
            return Result::fail($validator->errors()->first());
        }
        $userId = auth('api')->id();
        # 查询持有收益
        $hold_income = UserGoodsLog::query()->where('user_goods_id', $params['user_goods_id'])
            ->where('user_id',$userId)->sum('income');
        # 查询收益明细
        $userGoodsLogData = UserGoodsLog::query()->where('user_goods_id', $params['user_goods_id'])
            ->where('user_id',$userId)
            ->orderByDesc('id')->get();
        return Result::success(['userGoodsData'=>$hold_income,'userGoodsLogData'=>$userGoodsLogData]);
    }
}
