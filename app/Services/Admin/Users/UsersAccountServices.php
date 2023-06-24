<?php

namespace App\Services\Admin\Users;

use App\Models\Base\Administrator;
use App\Models\User\Users;
use App\Models\User\UsersAccount;
use App\Models\User\UsersLog;
use App\Models\User\UsersLoginLog;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use jianyan\excel\Excel;

class UsersAccountServices
{

    /**
     * User: Yan
     * DateTime: 2023/2/24
     * @param $params
     * @return LengthAwarePaginator 用户账户列表
     * 用户列表
     */
    public function userAccountList($params): LengthAwarePaginator
    {
        $where = [];

        # 用户
        if (isset($params['user_id']) && $params['user_id']) {
            $where[] = ['user_id', '=', $params['user_id']];
        }
        # 类型
        if (isset($params['type']) && $params['type']) {
            $where[] = ['type', '=', $params['type']];
        }
        # 注册时间结束

        return UsersAccount::query()->with(['user' , 'toUserIdInfo'])->where($where)->orderBy('id', 'desc')->paginate(request()->query('limit', 15));
    }



}
