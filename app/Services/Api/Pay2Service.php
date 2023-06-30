<?php

namespace App\Services\Api;

class Pay2Service
{
    # 签名
    function generateSign($params, $secretKey): string
    {
        ksort($params);
        $plainText = '';
        foreach ($params as $key => $value) {
            if (!empty($value)) {
                $plainText .= $key . '=' . $value . '&';
            }
        }
        $plainText .= 'key=' . $secretKey;
        $stringSignTemp = $plainText . '&key=' . $secretKey;
        return strtoupper(md5($stringSignTemp));
    }

    # 统一下单 POST
    # https://anyimchagmof9rg0.zzbbm.xyz/api/anon/apidoc#doc1
    public function orderCreate(){
        $url = "https://anyipayih52ioq8d.zzbbm.xyz/api/pay/unifiedorder";
    }

    # 查询订单 POST
    public function orderQuery(){
        $url = "https://anyipayih52ioq8d.zzbbm.xyz/api/pay/query";
    }

}
