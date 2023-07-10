<?php

namespace App\Http\Controllers\Api;

# 首页控制器
use App\Models\AccumulateConfig;
use App\Models\Goods;
use App\Models\NewD;
use App\Utils\Result;
use Illuminate\Http\JsonResponse;

class IndexController
{

    # 首页数据
    public function index(): JsonResponse
    {
        # 获取当前页面的地址 不包括路由
        $url = env('APP_URL');
        # 首页视频
        $video = [$url . "/video/index.mp4", $url . "/video/video2.mp4", $url . "/video/video3.mp4"];
        #$video 随机去取一个
        $videoUrl = $video[array_rand($video)];
        $officialNews = NewD::query()->where('key_name', 'guanfang')->select(['name', 'content_text'])->first();
        $povertyAlleviation = NewD::query()->where('key_name', 'fupin')->select(['name', 'content_text'])->first();
        return Result::success([
            'videoUrl'           => $videoUrl,
            'officialNews'       => $officialNews,
            'povertyAlleviation' => $povertyAlleviation,
        ]);
    }


    # 福利介绍页
    public function welfare(): JsonResponse
    {
        $tuanDui = AccumulateConfig::query()->where('type', 1)->select(['id', 'key', 'num'])->get();
        $yaoQin = AccumulateConfig::query()->where('type', 2)->select(['id', 'key', 'num'])->get();
        # 收益信息
        $yangLao = Goods::query()->where('type', 1)->select(['id', 'amount', 'income', 'validity_day', 'end_rewards'])->get();
        foreach ($yangLao as &$v) {
            $v['totalAmount'] = bcadd(bcmul($v['income'], $v['validity_day'], 2), $v['end_rewards'], 2);
        }
        # 医疗
        $yiLao = Goods::query()->where('type', 2)->select(['id', 'amount', 'income', 'validity_day', 'end_rewards'])->get();
        foreach ($yiLao as &$v) {
            $v['totalAmount'] = bcadd(bcmul($v['income'], $v['validity_day'], 2), $v['end_rewards'], 2);
        }
        $XueXi = Goods::query()->where('type', 3)->select(['id', 'amount', 'income', 'validity_day', 'end_rewards'])->get();
        foreach ($XueXi as &$v) {
            $v['totalAmount'] = bcadd(bcmul($v['income'], $v['validity_day'], 2), $v['end_rewards'], 2);
        }

        return Result::success([
            'tuanDui' => $tuanDui,# 团队奖励信息
            'yaoQin'  => $yaoQin,# 邀请奖励信息
            'yangLao' => $yangLao,# 养老产品信息
            'yiLao'   => $yiLao,# 医疗产品信息
            'XueXi'   => $XueXi,# 教育产品信息
        ]);
    }
}
