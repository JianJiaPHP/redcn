<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Prompt\Prompt;
use App\Models\Prompt\PromptClass;
use App\Models\Prompt\PromptMidJourney;
use App\Models\Prompt\PromptUsersLike;
use App\Models\Prompt\UserMidjourney;
use App\Models\Prompt\UserPrompt;
use App\Utils\Result;
use DB;
use Exception;
use Illuminate\Http\JsonResponse;

class PromptController extends Controller
{
    /**
     * User: Yan
     * DateTime: 2023/5/12
     * @return JsonResponse
     * 查询预训练模型分类
     */
    public function getPromptCategory(): JsonResponse
    {
        $list = PromptClass::query()->where(['is_open' => 1, 'type' => 1])->select(['id', 'title'])->get();
        return Result::success($list);
    }

    /**
     * User: Yan
     * DateTime: 2023/5/12
     * @return JsonResponse 查询社区模型列表
     * 查询社区模型列表
     */
    public function getPrompt(): JsonResponse
    {
        $params = request()->all();
        $user = auth('api')->user();
        # 查询社区模型
        $where = [];
        if (!empty($params['class_id'])) {
            $where['class_id'] = $params['class_id'];
        }
        if (!empty($params['title'])) {
            $where[] = ['title', 'like', '%' . $params['title'] . '%'];
        }
        $list = Prompt::query()
            ->where($where)
            ->select(['id', 'title', 'class_id', 'subtitle', 'content', 'zan_num'])
            ->paginate(request()->query('limit', 30));
        foreach ($list as $k => $v) {
            $list[$k]['is_like'] = PromptUsersLike::query()->where('user_id', $user['id'])->where('prompt_id',$v['id'])->where('type', 1)->exists() ? 1 : 0;
            $list[$k]['is_collect'] = PromptUsersLike::query()->where('user_id', $user['id'])->where('prompt_id',$v['id'])->where('type', 2)->exists() ? 1 : 0;
        }
        return Result::success($list);
    }

    /**
     * User: Yan
     * DateTime: 2023/5/12
     * @return JsonResponse 收藏社区模型
     * 收藏社区模型
     */
    public function collectPrompt(): JsonResponse
    {
        $user = auth('api')->user();
        $params = request()->all();
        #验证器
        $validator = validator($params, [
            'prompt_id' => 'required|integer',
        ], [
            'prompt_id.required' => '模型id不能为空',
            'prompt_id.integer'  => '模型id必须为整数',
        ]);
        if ($validator->fails()) {
            return Result::fail($validator->errors()->first());
        }
        $promptData = Prompt::query()->where('id', $params['prompt_id'])->first();
        if (!$promptData) {
            return Result::fail('模型不存在');
        }
        $is = PromptUsersLike::query()->where('user_id', $user['id'])->where('prompt_id', $params['prompt_id'])->where('type', 2)->exists();
        if ($is) {
            return Result::fail('已收藏，请勿重复点击');
        }
        try {
            DB::beginTransaction();
            $likeRes = PromptUsersLike::query()->create([
                'user_id'   => $user['id'],
                'prompt_id' => $params['prompt_id'],
                'type'      => 2,
            ]);
            if (!$likeRes) {
                DB::rollBack();
                return Result::fail('收藏失败');
            }
            #添加至自己的模型
            $create = UserPrompt::query()
                ->create([
                    'title'     => $promptData['title'],
                    'subtitle'  => $promptData['subtitle'],
                    'content'   => $promptData['content'],
                    'user_id'   => $user['id'],
                    'prompt_id' => $params['prompt_id'],
                    'like_id'   => $likeRes['id']
                ]);
            if (!$create) {
                DB::rollBack();
                return Result::fail('收藏失败');
            }
            DB::commit();
            return Result::success();
        } catch (Exception $e) {
            return Result::fail($e->getMessage());
        }
    }

    /**
     * User: Yan
     * DateTime: 2023/5/12
     * @return JsonResponse
     * 删除我的模型也可以用 或在我的模型里面取消收藏
     */
    public function deleteCollectPrompt($id): JsonResponse
    {
        # 查询我的模型
        $user = auth('api')->user();
        # 查询中间表 获取收藏id
        $promptData = UserPrompt::query()->where('id', $id)->where('user_id', $user['id'])->first();
        if (!$promptData) {
            return Result::fail('模型不存在');
        }
        PromptUsersLike::query()->where('id', $promptData['like_id'])->delete();
        UserPrompt::query()->where('id', $promptData['id'])->delete();
        return Result::success();
    }

    /**
     * User: Yan
     * DateTime: 2023/5/19
     * @param $id
     * @return JsonResponse 通过社区模型id 取消收藏
     * 通过社区模型id 取消收藏
     */
    public function deletePromptCollectPrompt($id): JsonResponse
    {
        $user = auth('api')->user();
        # 查询中间表 获取收藏id
        $promptData = PromptUsersLike::query()->where('prompt_id', $id)->where('user_id', $user['id'])->where('type', 2)->first();
        if (!$promptData) {
            return Result::fail('模型不存在');
        }
        PromptUsersLike::query()->where('id', $promptData['id'])->delete();
        UserPrompt::query()->where('like_id', $promptData['id'])->delete();
        return Result::success();
    }

    /**
     * User: Yan
     * DateTime: 2023/5/12
     * @return JsonResponse 点赞社区模型
     * 点赞社区模型
     */
    public function likePrompt(): JsonResponse
    {
        $params = request()->all();
        #验证器
        $validator = validator($params, [
            'prompt_id' => 'required|integer',
        ], [
            'prompt_id.required' => '模型id不能为空',
            'prompt_id.integer'  => '模型id必须为整数',
        ]);
        if ($validator->fails()) {
            return Result::fail($validator->errors()->first());
        }
        $user = auth('api')->user();
        # 查询是否存在
        $promptData = Prompt::query()->where('id', $params['prompt_id'])->exists();
        if (!$promptData) {
            return Result::fail('模型不存在');
        }
        #查询我是否点赞过
        $is = PromptUsersLike::query()->where('user_id', $user['id'])
            ->where('prompt_id', $params['prompt_id'])
            ->where('type', 1)
            ->exists();
        if ($is) {
            return Result::fail('已点赞，请勿重复点击');
        }
        try {
            DB::beginTransaction();
            $likeRes = PromptUsersLike::query()->create([
                'user_id'   => $user['id'],
                'prompt_id' => $params['prompt_id'],
                'type'      => 1,
            ]);
            if (!$likeRes) {
                DB::rollBack();
                return Result::fail('点赞失败');
            }
            #点赞数+1
            $promptData = Prompt::query()->where('id', $params['prompt_id'])->increment('zan_num');
            if (!$promptData) {
                DB::rollBack();
                return Result::fail('点赞失败');
            }
            DB::commit();
            return Result::success();
        } catch (Exception $e) {
            return Result::fail($e->getMessage());
        }
    }

    /**
     * User: Yan
     * DateTime: 2023/5/12
     * @param $id
     * @return JsonResponse
     * 取消点赞
     */
    public function deleteLikePrompt($id): JsonResponse
    {
        # 查询我的模型
        $user = auth('api')->user();
        # 查询我的点赞记录
        $promptData = PromptUsersLike::query()->where('user_id', $user['id'])->where('prompt_id', $id)->where('type', 1)->first();
        if (!$promptData) {
            return Result::fail('未点赞');
        }
        PromptUsersLike::query()->where(['user_id' => $user['id'], 'prompt_id' => $id])->delete();
        Prompt::query()->where('id', $id)->decrement('zan_num');
        return Result::success();
    }

    /**
     * User: Yan
     * DateTime: 2023/5/12
     * @return JsonResponse 我的模型
     * 我的模型
     */
    public function myPrompt(): JsonResponse
    {
        $user = auth('api')->user();
        $list = UserPrompt::query()
            ->where('user_id', $user['id'])
            ->select(['id', 'title', 'subtitle', 'content', 'like_id'])
            ->paginate(request()->query('limit', 30));
        foreach ($list as $k => $v) {
            $list[$k]['prompt_id'] = PromptUsersLike::query()->where('id', $v['like_id'])->value('prompt_id');
        }
        return Result::success($list);
    }

    /**
     * User: Yan
     * DateTime: 2023/5/12
     * @return JsonResponse 新增我的模型
     * 新增我的模型
     */
    public function createPrompt(): JsonResponse
    {
        $params = request()->all();
        #验证器
        $validator = validator($params, [
            'title'    => 'required|string|max:255',
            'subtitle' => 'required|string|max:255',
            'content'  => 'required|string',
        ], [
            'title.required'    => '标题不能为空',
            'title.string'      => '标题必须为字符串',
            'title.max'         => '标题最大长度为255',
            'subtitle.required' => '副标题不能为空',
            'subtitle.string'   => '副标题必须为字符串',
            'subtitle.max'      => '副标题最大长度为255',
            'content.required'  => '内容不能为空',
            'content.string'    => '内容必须为字符串',
        ]);
        if ($validator->fails()) {
            return Result::fail($validator->errors()->first());
        }
        $user = auth('api')->user();
        $res = UserPrompt::query()->create([
            'title'    => $params['title'],
            'subtitle' => $params['subtitle'],
            'content'  => $params['content'],
            'user_id'  => $user['id']
        ]);
        if (!$res) {
            return Result::fail('新增失败');
        }
        return Result::success();
    }


    /**
     * User: Yan
     * DateTime: 2023/5/24
     * @return JsonResponse
     * midJourney社区分类信息
     */
    public function midJourneyClass(): JsonResponse
    {
        # 查询1 2级树
        $list = PromptMidJourney::query()->where(['level' => 1, 'p_id' => 0])->select(['id', 'p_id', 'title', 'level'])->get();
        foreach ($list as $k => $v) {
            $list[$k]['children'] = PromptMidJourney::query()->where('p_id', $v['id'])->select(['id', 'p_id', 'title', 'level'])->get();
        }
        return Result::success($list);
    }

    /**
     * User: Yan
     * DateTime: 2023/5/24
     * @return JsonResponse
     * midJourney社区图片列表
     */
    public function midJourneyClassList(): JsonResponse
    {
        $params = request()->all();
        #验证器
        $validator = validator($params, [
            'p_id' => 'required|integer',
        ], [
            'p_id.required' => '分类id不能为空',
            'p_id.integer'  => '分类id必须为整数',
        ]);
        if ($validator->fails()) {
            return Result::fail($validator->errors()->first());
        }
        $list = PromptMidJourney::query()->where(['level' => 3, 'p_id' => $params['p_id']])
            ->orderByDesc('id')->paginate(request()->query('limit', 30));
        return Result::success($list);
    }

    /**
     * User: Yan
     * DateTime: 2023/5/24
     * @return JsonResponse
     * 我的收藏midJourney
     */
    public function myMidJourney(): JsonResponse
    {
        $user_id = auth('api')->id();
        $list = UserMidjourney::query()->where('user_id', $user_id)->orderByDesc('id')->paginate(request()->query('limit', 30));
        return Result::success($list);
    }

    /**
     * User: Yan
     * DateTime: 2023/5/24
     * @return JsonResponse
     * 收藏midJourney
     */
    public function collectMidJourney(): JsonResponse
    {
        $params = request()->all();
        #验证器
        $validator = validator($params, [
            # 图片地址 oss_url 是否是字符串 是否是http地址 是否是图片类型
            'oss_url' => 'required|string|url',
        ], [
            'oss_url.required' => '图片地址不能为空',
            'oss_url.string'   => '图片地址必须为字符串',
            'oss_url.url'      => '图片地址必须为http地址',
//            'oss_url.image'    => '图片地址必须为图片类型',
        ]);
        if ($validator->fails()) {
            return Result::fail($validator->errors()->first());
        }

        $user_id = auth('api')->id();
        $arr = [
            'user_id' => $user_id,
            'oss_url' => $params['oss_url'],
        ];
        if (!empty($params['mid_journey_id'])) {
            # 判断是否收藏
            $isCollect = UserMidjourney::query()->where(['user_id' => $user_id, 'mid_journey_id' => $params['mid_journey_id']])->exists();
            if ($isCollect) {
                return Result::success('已收藏');
            }
            $arr['mid_journey_id'] = $params['mid_journey_id'];
        }
        $res = UserMidjourney::query()->create($arr);
        if (!$res) {
            return Result::fail('收藏失败');
        }
        return Result::success();
    }


    /**
     * User: Yan
     * DateTime: 2023/5/24
     * @return JsonResponse
     * 删除收藏midJourney
     */
    public function delCollectMidJourney(): JsonResponse
    {
        $params = request()->all();
        # 验证器
        $validator = validator($params, [
            # id 数组 必须
            'id' => 'required|array',
        ], [
            'id.required' => 'id不能为空',
            'id.array'    => 'id必须为数组',
        ]);
        if ($validator->fails()) {
            return Result::fail($validator->errors()->first());
        }
        UserMidjourney::query()->whereIn('id', $params['id'])->delete();
        return Result::success();
    }

    /**
     * 聊天记录删除收藏
     */
    public function delMjCollect($id): JsonResponse
    {
        UserMidjourney::query()->where('mid_journey_id', $id)->delete();
        return Result::success();
    }

}
