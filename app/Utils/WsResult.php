<?php


namespace App\Utils;


use Illuminate\Http\JsonResponse;

/**
 * 返回
 * Class Result
 * @package App\Utils
 */
class WsResult
{
    /**
     * 成功返回code
     */
    private const SUCCESS = 200;
    /**
     * 失败返回code
     */
    private const FAIL = 500;
    /**
     * 暂未登录或token已经过期
     */
    private const UNAUTHORIZED = 401;
    /**
     * 没有相关权限
     */
    private const FORBIDDEN = 403;
    /**
     * 参数验证失败
     */
    private const VALIDATE_FAILED = 422;
    /**
     * 签名验证失败
     */
    private const SIGN_FAILED = 101;

    /**
     * 判断返回
     * @param $data
     * @return string
          */
    public static function choose($data)
    {
        return $data ? self::success($data) : self::fail();
    }

    /**
     * 成功返回
     * @param null $data
     * @param string $message
     * @return string
          */
    public static function success($data = null, string $message = 'success')
    {
        return self::result(self::SUCCESS, $message, $data);
    }

    /**
     * 返回
     * @param $code
     * @param $message
     * @param null $data
          */
    private static function result($code, $message, $data = null): string
    {
        return json_encode([
            'code'    => $code,
            'message' => $message,
            'data'    => $data
        ])??'';
    }

    /**
     * 失败返回
     * @param string $message
     * @param null $data
     * @return string
          */
    public static function fail(string $message = 'fail', $data = null)
    {
        return self::result(self::FAIL, $message, $data);
    }

    /**
     * 暂未登录或token已经过期
     * @return string
          */
    public static function unauthorized()
    {
        return self::result(self::UNAUTHORIZED, '登录已过期请重新登录');
    }

    /**
     * 没有相关权限
     * @return string
          */
    public static function forbidden()
    {
        return self::result(self::FORBIDDEN, "没有相关权限");
    }

    /**
     * 参数验证失败
     * @param $message
     * @return string
          */
    public static function validateFailed($message)
    {
        return self::result(self::VALIDATE_FAILED, $message);
    }

    /**
     * 签名验证失败
     * @return string
          */
    public static function signFailed()
    {
        return self::result(self::SIGN_FAILED, '签名失败');
    }
}
