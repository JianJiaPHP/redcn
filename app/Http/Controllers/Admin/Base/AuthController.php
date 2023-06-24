<?php

namespace App\Http\Controllers\Admin\Base;

use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoginGoogleRequests;
use App\Http\Requests\LoginRequests;
use App\Services\Admin\Base\AdministratorService;
use App\Utils\Result;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Validator;

class AuthController extends Controller
{
    private AdministratorService $administratorService;

    /**
     * AuthController constructor.
     */
    public function __construct(AdministratorService $administratorService)
    {
        $this->administratorService = $administratorService;
    }


    /**
     * 登录
     * @param LoginRequests $loginRequests
     * @return JsonResponse
     * author Yan
     * @throws ApiException
     */
    public function login(LoginRequests $loginRequests): JsonResponse
    {
        $params = $loginRequests->validated();

        $ips = request()->getClientIp();

        $data = $this->administratorService->login($params['account'], $params['password'], $ips);

        return Result::success($data);
    }

    /**
     * User: Yan
     * DateTime: 2023/2/22
     * @return JsonResponse
     * 退出登录
     */
    public function logout(): JsonResponse
    {
        return Result::choose($this->administratorService->logout());
    }


}
