<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Base\Message;
use App\Utils\Result;
use Illuminate\Http\JsonResponse;

class MessageController extends Controller
{

    /**
     * User: Yan
     * DateTime: 2023/5/18
     * @return JsonResponse
     * 我的消息
     */
    public function myMessage(): JsonResponse
    {
        $userId = auth('api')->id();
        $data = Message::query()->where('user_id',$userId)->orderByDesc('id')->paginate(request()->query('limit', 30));
        return Result::success($data);
    }

}
