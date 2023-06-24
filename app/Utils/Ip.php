<?php


namespace App\Utils;


use App\Helpers\HttpHelper;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class Ip
{
    use HttpHelper;

    /**
     * 根据ip获取详细信息
     * @param string $ip
     * @return array
     * author II
     */
    public static function getIpInfo(string $ip): array
    {
        try {
            $client = new Client(['timeout' => 5.0]);
            $response = $client->request('GET',
                "https://ip.useragentinfo.com/json?ip=".$ip
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
