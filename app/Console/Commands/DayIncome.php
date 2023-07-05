<?php

namespace App\Console\Commands;


use App\Models\UserGoods;
use App\Services\Api\UserAccountService;
use App\Utils\Result;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Http\JsonResponse;
use Log;


class DayIncome extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:DayIncome';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '每日产品收益';

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
     * @return JsonResponse|void
     */
    public function handle(UserAccountService $apiServiceProvider)
    {
        Log::info('每日产品收益开始' . Carbon::now());
        try {
            # 查询所有在使用的产品
            $arr = UserGoods::query()->where('status', 1)
                ->where('end_date', '>=', Carbon::now())
                ->select(['id', 'user_id', 'income','end_rewards','end_date'])->get();
            foreach ($arr as $v) {
                $apiServiceProvider->productIncome($v);
            }
            return Result::success();
        } catch (\Exception $e) {
            Log::error('每日产品收益开始error' . Carbon::now() . $e->getMessage());
            return Result::fail($e->getMessage());
        }
    }
}
