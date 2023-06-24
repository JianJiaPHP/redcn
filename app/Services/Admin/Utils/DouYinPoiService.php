<?php

namespace App\Services\Admin\Utils;

use App\Utils\DouYinSendApi;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Redis;

class DouYinPoiService
{
    /**
     * User: Yan
     * DateTime: 3/7
     * @param $params
     * @return array|string
     * 获取抖音POI id列表
     * @throws Exception|GuzzleException
     */
    public function getPoiList($params)
    {
        //生成 client_token
        try {
            $douYinSendApi = new DouYinSendApi();
            $client_token = $douYinSendApi->post_client_token();
            if (!$client_token) {
                throw new Exception('获取client_token失败');
            }
            //获取抖音POI id列表
            $list = $douYinSendApi->get_poi_search_keyword($client_token, $params);
            if ($list['data']['error_code'] != 0) {
                Redis::del('douyin_client_token');
                throw new Exception("获取抖音数据失败,请重新搜索");
            }
            return $list['data'];
        } catch (GuzzleException $e) {
            return $e->getMessage();
        }
    }
}
