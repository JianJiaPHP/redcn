<?php

namespace App\Http\Controllers;

use App\Utils\DouYinSendApi;
use App\Utils\DouYinSendApiServiceProvider;
use GuzzleHttp\Client;
use Yansongda\Pay\Pay;

class Ping extends Controller
{

    public function index()
    {
       $s = new DouYinSendApiServiceProvider();
       $S1 = $s->getClientToken();
       dd($S1);
    }


}
