<?php

namespace App\Console\Commands;

use App\Models\Store\StoreSalesroom;
use App\Utils\DouYinSendApiServiceProvider;
use App\Utils\Result;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Http\JsonResponse;
use Log;


class SupplierQueryTask extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:SupplierQueryTask';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '更新抖音同步门店信息';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @param DouYinSendApiServiceProvider $apiServiceProvider
     * @return JsonResponse|void
     */
    public function handle(DouYinSendApiServiceProvider $apiServiceProvider)
    {
        Log::info('更新抖音同步门店信息start' . Carbon::now());
        try {
            # 查询店铺未匹配上的任务
            $arr = StoreSalesroom::query()->where('status', 1)->orWhere('status', 0)->pluck('task_id')->toArray();
            foreach ($arr as $v) {
                $apiServiceProvider->supplierQueryTask($v);
            }
        } catch (\Exception $e) {
            Log::error('更新抖音同步门店信息error' . Carbon::now() . $e->getMessage());
            return Result::fail($e->getMessage());
        }
    }
}
