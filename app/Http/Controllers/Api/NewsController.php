<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\News\News;
use App\Utils\Result;
use DB;
use Illuminate\Http\JsonResponse;
use Validator;

class NewsController extends Controller
{

    /**
     * 查询新闻咨询列表
     */
    public function newList(): JsonResponse
    {
        $list = News::query()->where(['class_id' => 1])->select('id', 'key_name', 'name', DB::raw('SUBSTRING(content_text, 1, 300) as content_text'), 'address', 'created_at')
            ->orderByDesc('created_at')->paginate(request()->query('limit', 15));
        foreach ($list as $k => $v) {
            // 去除 HTML 标签
            $plainText = strip_tags($v['content_text']);
            // 将特殊字符转换为原始字符
            $plainText = htmlspecialchars_decode($plainText);
            # 去除空格还是有&nbsp;存在，所以用下面的方式去除
            $plainText = str_replace('&nbsp;', '', $plainText);
            $list[$k]['content_text'] = $plainText;
        }
        return Result::success($list);
    }

    /**

     * DateTime: 2023/5/23
     * @return JsonResponse
     * 获取协议内容接口
     */
    public function getAgreement(): JsonResponse
    {
        $params = request()->all();
        # 验证器
        $validator = Validator::make($params, [
            'key' => 'required|string',
        ], [
            'key.required' => 'key不能为空',
            'key.string'   => 'key必须是字符串',
        ]);
        if ($validator->fails()) {
            return Result::fail($validator->errors()->first());
        }
        $data = News::query()->where(['key_name' => $params['key'], 'class_id' => 2])
            ->select('id', 'key_name', 'name', 'content_text', 'address', 'created_at', 'updated_at')
            ->first();
        if (!$data) {
            return Result::fail("暂无数据");
        }
        return Result::success($data);
    }
}
