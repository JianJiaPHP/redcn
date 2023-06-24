<?php


namespace App\Http\Middleware;


use App\Models\Base\AdminResource;
use App\Models\Base\AdminRoleAdministrator;
use App\Utils\Result;
use Closure;
use Illuminate\Support\Str;

class PermissionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (!auth('api')->check()) {
            return Result::unauthorized();
        }
        $userId = auth('api')->id();
        // 获取管理员信息
        $roleIds = AdminRoleAdministrator::getRoleIdsByAdministratorId($userId);
        //为1是超级管理员 直接通过
        if (in_array(1, $roleIds)) {
            return $next($request);
        }
        $route = $request->route()->uri;
        $path = Str::after($route, "");
        // 获取所有资源
        $resource = AdminResource::getAdminResourceByAdministratorId($userId);

        $method = $request->method();
        foreach ($resource as $v) {
            $httpMethod = explode(',', $v['http_method']);
            if ((in_array('*', $httpMethod) || in_array($method, $httpMethod)) && Str::is($v['url'], $path)) {
                return $next($request);
            }
        }
        return Result::forbidden();

    }
}
