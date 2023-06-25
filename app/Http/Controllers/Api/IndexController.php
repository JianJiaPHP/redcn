<?php

namespace App\Http\Controllers\Api;

# 首页控制器
use Illuminate\Http\JsonResponse;

class IndexController
{

    # 邀请奖励明细
    public function inviteLog(): JsonResponse
    {
        $params = request()->all();
        $userId = auth('api')->id();

    }
}
