<?php


namespace App\Utils;


use App\Helpers\HttpHelper;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class HttpSend
{
    use HttpHelper;

    /**
     * post请求
     * @param $url
     * @param $data
     * @return array|bool|mixed
     * author Yan
     */
    public static function post($url, $data)
    {
        try {
            $client = new Client(['timeout' => 5.0]);
            $response = $client->request('POST',
                $url,
                [
                    "form_params" => [
                        'params' => $data
                    ]
                ]
            );
            $result = self::getContentFromResponse($response);
            if (!$result) {
                return [];
            }
            return $result;
        } catch (Exception|GuzzleException $exception) {
            return [];
        }
    }
}
