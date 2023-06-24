<?php


namespace App\Http\Middleware;


use App\Utils\Result;
use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\JWTAuth;

class ApiUserMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (!auth('api')->check()) {
            return Result::unauthorized();
        }
        $userId = auth('api')->id();
        //如果没有就重新登录
        if (!$userId) {
            return Result::unauthorized();
        }
        return $next($request);
    }

}
