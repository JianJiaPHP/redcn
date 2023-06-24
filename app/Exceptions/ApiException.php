<?php


namespace App\Exceptions;


use Exception;

/**
 * api异常
 * Class ApiException
 * @package App\Exceptions
 */
class ApiException extends Exception
{
    /**
     * 有参构造
     * ApiException constructor.
     * @param string $message
     */
    public function __construct($message = "")
    {
        parent::__construct($message);
    }


}
