<?php


namespace App\Utils;

use App\Exceptions\ApiException;
use App\Helpers\HttpHelper;
use App\Models\chat\MjGptLog;
use App\Services\Api\ChatGptService;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Facades\Redis;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ChatApi
{

}
