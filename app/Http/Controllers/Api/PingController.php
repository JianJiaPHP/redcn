<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

class PingController extends Controller
{

    public function index()
    {
        $params = request()->all();
        \Log::info('ping', $params);
    }


}
