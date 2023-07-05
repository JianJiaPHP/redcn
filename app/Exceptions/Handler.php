<?php

namespace App\Exceptions;

use App\Utils\Result;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Report or log an exception.
     *
     * @param Throwable $e
     * @return void
     */
    public function report(Throwable $e)
    {
        try {
            parent::report($e);
        } catch (Throwable $throwable) {
            //

        }
    }

    /**
     * 截取异常
     */
    public function render($request, Throwable $e)
    {
        if ($e instanceof ApiException) {
            return Result::fail($e->getMessage());
        }

        return parent::render($request, $e);
    }
}
