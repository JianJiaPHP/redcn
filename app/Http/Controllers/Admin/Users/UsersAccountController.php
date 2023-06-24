<?php

namespace App\Http\Controllers\Admin\Users;

use App\Http\Controllers\Controller;
use App\Services\Admin\Users\UsersAccountServices;
use App\Utils\Result;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\Exception as PhpSpreadsheetException;
use Validator;

class UsersAccountController extends Controller
{

    protected UsersAccountServices $usersAccountServices;

    public function __construct(\App\Services\Admin\Users\UsersAccountServices $usersAccountServices)
    {
        $this->usersAccountServices = $usersAccountServices;
    }

    /**
     * User: Yan
     * DateTime: 2023/2/24
     * @return JsonResponse
     * 用户列表
     */
    public function index(): JsonResponse
    {
        $params = request()->all();

        $data = $this->usersAccountServices->userAccountList($params);

        return Result::success($data);
    }

}
