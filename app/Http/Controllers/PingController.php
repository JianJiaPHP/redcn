<?php

namespace App\Http\Controllers;

use App\Models\UserGoods;
use App\Services\Api\UserAccountService;
use Carbon\Carbon;

class PingController extends Controller
{

    public function index()
    {
        $arr = UserGoods::query()->where('status', 1)
            ->where('end_date', '>=', Carbon::now())
            ->select(['id', 'user_id', 'income','end_rewards','end_date'])->get();
        $apiServiceProvider = new UserAccountService();
        foreach ($arr as $v) {
            $apiServiceProvider->productIncome($v);
        }
    }


}
