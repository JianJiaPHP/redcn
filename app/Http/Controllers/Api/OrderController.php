<?php

namespace App\Http\Controllers\Api;

# 订单管理
use App\Models\UserGoods;

class OrderController
{

    # 产品购买记录
    public function payLog(){
        $userId = auth('api')->id();
        $userGoodsData = UserGoods::query()
            ->where([
                'user_id'=>$userId
            ])
            ->orderByDesc('id')->get();
    }
}
