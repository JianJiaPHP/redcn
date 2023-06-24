<?php


namespace App\Helpers;


use Psr\Http\Message\ResponseInterface;

trait HttpHelper
{
    /**
     * POST GET 请求返回
     * @param ResponseInterface $response
     * @return bool|mixed
     * @author Aii
     * @date 2019/12/16 上午11:17
     */
    protected static function getContentFromResponse(ResponseInterface $response)
    {
        if ($response->getStatusCode() === 200) {
            return json_decode($response->getBody()->getContents(), true);
        }

        return false;
    }

}
