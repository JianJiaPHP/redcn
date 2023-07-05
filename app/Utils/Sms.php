<?php


namespace App\Utils;


use AlibabaCloud\SDK\Dysmsapi\V20170525\Dysmsapi;
use AlibabaCloud\SDK\Dysmsapi\V20170525\Models\SendSmsRequest;
use Darabonba\OpenApi\Models\Config;

class Sms
{
    public static function sendCode($phone, $code)
    {
        $signName = "玛姬传媒";
        $templateCode = "SMS_460650916";
        $templateParam = "{\"code\":\"$code\"}";
        return self::send($phone, $signName, $templateCode, $templateParam);
    }

    /**
     * 短信发送
     * @param $phone string 验证码
     * @param $signName string 签名
     * @param $templateCode string 模版
     * @param $templateParam string 模块变量
          */
    private static function send($phone, $signName, $templateCode, $templateParam)
    {
        $client = self::createClient();
        $sendSmsRequest = new SendSmsRequest([
            "phoneNumbers"  => $phone,
            "signName"      => $signName,
            "templateCode"  => $templateCode,
            "templateParam" => $templateParam
        ]);
        // 复制代码运行请自行打印 API 的返回值
        return $client->sendSms($sendSmsRequest);
    }

    /**
     * 使用AK&SK初始化账号Client
     * @return Dysmsapi
          */
    private static function createClient()
    {
        $smsConfig = \config('aliyun.sms');
        $config = new Config([
            // 您的AccessKey ID
            "accessKeyId"     => $smsConfig['accessKeyId'],
            // 您的AccessKey Secret
            "accessKeySecret" => $smsConfig['accessKeySecret']
        ]);
        // 访问的域名
        $config->endpoint = "dysmsapi.aliyuncs.com";
        return new Dysmsapi($config);
    }


}
