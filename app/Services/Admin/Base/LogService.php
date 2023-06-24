<?php


namespace App\Services\Admin\Base;


use App\Models\Base\AdminLoginLog;
use App\Models\Base\AdminOperatingLogs;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class LogService
{

    /**
     * 操作日志
     * @param $keyword
     * @param $limit
     * @return LengthAwarePaginator
     * author Yan
     */
    public function operatingLog($keyword, $limit): LengthAwarePaginator
    {
        return AdminOperatingLogs::list($keyword, $limit);
    }

    /**
     * 登陆日志列表
     * @param $keyword
     * @param $limit
     * @return LengthAwarePaginator
     * author Yan
     */
    public function loginLog($keyword, $limit)
    {
        return AdminLoginLog::list($keyword, $limit);
    }
}
