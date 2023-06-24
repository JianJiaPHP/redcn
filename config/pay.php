<?php

declare(strict_types=1);


return [
    'alipay' => [
        'default' => [
            // 必填-支付宝分配的 app_id
//            'app_id'                  => env('ZHIFUBAO_APPID', ''),
            'app_id'                  => '2021000122672062',
            // 必填-应用私钥 字符串或路径
            'app_secret_cert'         => "MIIEvQIBADANBgkqhkiG9w0BAQEFAASCBKcwggSjAgEAAoIBAQCemiuErGt0R5o9g+uVvOVjMSXlCnaiiPC5QfEui9GQq0U6Orc8omrrVHbvknkPXOiNOF9hXXn+jOKftYEJuhdsv6o1nKypYaDplfb7EhCNpRV8P2g6tDbpHcidl46an/DaklouapYsmGQpg5WYc0bfuWvU2Fea0mk2zFzPv+U4IkJzRwgn+9dpi/1cV6pFmPKy0WsJiUrmQkbl8+VDzKGV0mCSRPaMfxDD76gSwy/w/STguuhcz6Zw5XA1Jroix35c8wZFiznj2RSWNyKmVG6/eorGcrE+CJPfKaip6+XUfT+joXz9tXiDdocfG+cSWGxbkelpNZt2+REFnVFt3H3zAgMBAAECggEAb9Zc24hNmUgoXjpY9FSoGEL+6rxvHXuc67WxIVZcpzvua/odXbdV163O1G6fyRKFtZdLwreMn8+uulndrQM5I2kf2AArzKDYO/6Qk0iQvaDsPpnjoImVstpara3CVAx2ZVoQF2z3imjHMHLmGQMotfflLsiDNdwrbwW6aJBzTRnWHUdrV4lDDGUtvxUd/U0JnG5jf2sFu8azeKV8kUI9RKbxjLyME5HqcFfZP2IcgdoK8oBJrpfD94Y97E+h/mSaU48EQ7/Cp51tQ1dZP02bn3FU1kovaw3mUe4NZMjrqpUbzlgGBJ8Qdg3+xf+e6sldAUwg9My+PwLLqsXQlOiy4QKBgQDcSMtwmKdaYHO9Skho3JAu9KhAd/VgHuC9s8QNy/XWpI0eQy15QA5za+wus0HpMe1YUWLnikq8Y0191f4CdTDq0MNPDcVb7Cwb4NS9XMLcXHkQWp47xnZ/NJDxRfGeao7SikuJrwVEZFBsrtNURx8Dkwrgs1z1VxUU/c11AAVZsQKBgQC4USx3ipti1z+aQj/BB0ystlVZqskVxFkz+p069cx8eaA03b8DNLVcUDqYVoSP2insvecLfgFNWSWDccIBttjXmxdqIta4wv0HruqSUgitr3llruRMT/MrryolEyoqgACSVsapd4fz4O/dx8ddDWBPLmp+JkjXbZaapEvoF2TW4wKBgQCCPLui/236XgovwOpcrQLF8DpFm2bEhJ0FK3JfYdYgKDuSx87r3/ZrQURrO+pdD/que7fJAxc6mE9pdYpvM5BPVEwUwiSMO268sMm2h1Bh7n5ZL+pblvPaM176dwrqST/VtJQCfCrWC6UNDLO/ISlx+I622PS9zNWXCpNdeDyxkQKBgDeIrZ8u8fZr6EvwHgloy59WKI0lugSG7mGM5wa2vG/crXqAPWcX4HT5702q87HsB6m+5fg+dOH7NCRQiCXAE8GShxGBgbSVe2tvNlnzwY2jSy5p7XtYs2m8EzSse43ml0WAQ8cXrqy6X0sxQE43E4eH8qojmtrdt/zL99rsAYQFAoGAZ2zGC1eX4zME9TF31kWJneZIXeJXNdzwlqe3MuHzOWI8klPKalQduksPRh//0La4kpavYjbRV2s3IKTnFDEuoCzXM2hzRidVR8B56Hn8/04Kr4AUPSklmhJ7w/KZ26UbAgiMipZNdkjGv3qO4j+YQquHd2AEZf7F0NE7IdRkFFs=",
            // 必填-应用公钥证书 路径
            'app_public_cert_path'    => config_path('/ail2/appPublicCert (5).crt'),
            // 必填-支付宝公钥证书 路径
            'alipay_public_cert_path' => config_path('/ail2/appPublicCert (6).crt'),
            // 必填-支付宝根证书 路径
            'alipay_root_cert_path'   => config_path('/ail2/alipayRootCert (5).crt'),
            'return_url'              => env('APP_URL') . '/api/return',
            'notify_url'              => env('APP_URL') . '/api/notify',
            // 选填-服务商模式下的服务商 id，当 mode 为 Pay::MODE_SERVICE 时使用该参数
            'service_provider_id'     => '',
            'app_auth_token'          => '',
            // 选填-默认为正常模式。可选为： MODE_NORMAL, MODE_SANDBOX, MODE_SERVICE
//            'mode'                    => Pay::MODE_SANDBOX,
        ],
    ],
    'wechat' => [
        'default' => [
            // 必填-商户号，服务商模式下为服务商商户号
            'mch_id'                  => '',
            // 必填-商户秘钥
            'mch_secret_key'          => '',
            // 必填-商户私钥 字符串或路径
            'mch_secret_cert'         => '',
            // 必填-商户公钥证书路径
            'mch_public_cert_path'    => '',
            // 必填
            'notify_url'              => '',
            // 选填-公众号 的 app_id
            'mp_app_id'               => '',
            // 选填-小程序 的 app_id
            'mini_app_id'             => '',
            // 选填-app 的 app_id
            'app_id'                  => '',
            // 选填-合单 app_id
            'combine_app_id'          => '',
            // 选填-合单商户号
            'combine_mch_id'          => '',
            // 选填-服务商模式下，子公众号 的 app_id
            'sub_mp_app_id'           => '',
            // 选填-服务商模式下，子 app 的 app_id
            'sub_app_id'              => '',
            // 选填-服务商模式下，子小程序 的 app_id
            'sub_mini_app_id'         => '',
            // 选填-服务商模式下，子商户id
            'sub_mch_id'              => '',
            // 选填-微信公钥证书路径, optional，强烈建议 php-fpm 模式下配置此参数
            'wechat_public_cert_path' => [
                '45F59D4DABF31918AFCEC556D5D2C6E376675D57' => __DIR__ . '/Cert/wechatPublicKey.crt',
            ],
            // 选填-默认为正常模式。可选为： MODE_NORMAL, MODE_SERVICE
//            'mode'                    => Pay::MODE_NORMAL,
        ],
    ],
    'http'   => [ // optional
        'timeout'         => 5.0,
        'connect_timeout' => 5.0,
        // 更多配置项请参考 [Guzzle](https://guzzle-cn.readthedocs.io/zh_CN/latest/request-options.html)
    ],
    // optional，默认 warning；日志路径为：sys_get_temp_dir().'/logs/yansongda.pay.log'
    'logger' => [
        'enable'   => true,
        'file'     => storage_path() .'/logs/pay.log',
        'level'    => 'debug', // 建议生产环境等级调整为 info，开发环境为 debug
        'type'     => 'single', // optional, 可选 daily.
        'max_file' => 30, // optional, 当 type 为 daily 时有效，默认 30 天
    ],
];
