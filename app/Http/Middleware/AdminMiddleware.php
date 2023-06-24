<?php

namespace App\Http\Middleware;

use App\Utils\Result;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class AdminMiddleware
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
        if (!auth('admin')->check()) {
            return Result::fail(401,'请重新登录');
        }
        $user = auth('admin')->user();
        //如果没有就重新登录
        if (!$user) {
            return Result::fail(401,'请重新登录');
        }
        return $next($request);
    }

}
