<?php


namespace App\Http\Controllers\Admin\Base;


use App\Http\Controllers\Controller;
use App\Utils\Result;
use Illuminate\Http\JsonResponse;

class PayController extends Controller
{

    # 支付回调
    public function notify(): JsonResponse
    {
        $params = request()->all();
        \Log::info('支付回调参数', $params);
        return Result::success($params);
    }


}
