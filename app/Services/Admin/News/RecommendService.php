<?php


namespace App\Services\Admin\News;


use App\Models\News\RecommendData;
use App\Models\Store\Store;
use App\Models\Talent\AuthSage;
use DB;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Log;

class RecommendService
{
    /**
     * User: Yan
     * DateTime: 2023/3/7
     * @param $params
     * @return LengthAwarePaginator
     * 查询分类列表
     */
    public function list($params): LengthAwarePaginator
    {
        $where = [];
        # 结束时间的时间区间
        if (!empty($params['start_time']) && !empty($params['end_time'])) {
            $where[] = ['created_at', '>=', $params['start_time']];
            $where[] = ['created_at', '<=', $params['end_time']];
        }
        # 位置
        if (!empty($params['position'])) {
            $where[] = ['position', '=', $params['position']];//1:首页人气热搜 2:首页导师推荐 3:首页达人推荐 4:首页店铺推荐 5：学堂抖平台导师 6:学堂星选达人
        }
        # userid
        if (!empty($params['user_id'])) {
            $where[] = ['user_id', '=', $params['user_id']];
        }
        $where['is_false'] = 0;
        $data = RecommendData::query()->where($where)->orderBy('id', 'desc');

        return $data->paginate(request()->query('limit', 15));
    }


    /**
     * User: Yan
     * DateTime: 2023/3/7
     * @param $params
     * @return array
     */
    public function create($params): array
    {
        try {
            DB::beginTransaction();
            $add = $this->getAdd($params);
            $res = RecommendData::query()->create($add);
            if (!$res) {
                DB::rollBack();
                return ['code' => 500, 'msg' => "添加失败"];
            }
            DB::commit();
            return ['code' => 200, 'msg' => "添加成功"];
        } catch (Exception $exception) {
            Log::error("NewsService->create:" . $exception->getMessage());
            return ['code' => 500, 'msg' => $exception->getMessage()];
        }

    }

    /**
     * User: Yan
     * DateTime: 2023/3/6
     * @param $params
     * @return array
     */
    public function getAdd($params): array
    {
        $add['is_false'] = 0;
        $add['is_open'] = $params['is_open'] ?? -1;
        $add['value'] = $params['value'];
        $add['position'] = $params['position'];
        $add['user_id'] = $params['user_id'];
        # $params['position'] 等于 1或者5或者6
        if ($params['position'] == 1 || $params['position'] == 5 || $params['position'] == 6) {
            $add['is_money'] = 1;
        } else {
            $add['is_money'] = 0;
        }
        switch ($params['position']) {
            case 4:
            case 1:
                $add['type'] = 1;
                break;
            case 5:
            case 2:
                $add['type'] = 2;
                break;
            case 6:
            case 3:
                $add['type'] = 3;
                break;
        }
        if (!empty($params['user_id'])) {
            # 获取点赞量 和 关注量
            $arr['zan'] = 0;
            $arr['fans_number'] = 0;
            if ($add['position'] == 4) {
                # 获取商家简介
                $arr['introduction'] = Store::query()->where('user_id', $params['user_id'])->orderByDesc('id')->value('introduction');
            }
            $arrUser = AuthSage::query()->where(['user_id' => $params['user_id'], 'status' => 1])->orderBy('id', 'desc')->select(['fans_number', 'zan'])->first();
            if (!empty($arrUser)) {
                $arr['likes'] = $arrUser->zan;
                $arr['fans'] = $arrUser->fans_number;
                $arr['zan'] = $arrUser->zan;
                $arr['fans_number'] = $arrUser->fans_number;
            }
            $arr_value = json_decode($params['value'], true);
            $add['value'] = json_encode(array_merge($arr_value, $arr));
        }
        # 如果是
        return $add;
    }

    /**
     * User: Yan
     * DateTime: 2023/3/7
     * @param $id
     * @param $params
     * @return array 修改
     * 修改
     */
    public function update($id, $params): array
    {
        try {
            $add = $this->getAdd($params);
            $res = RecommendData::query()->where('id', $id)->update($add);
            if (!$res) {
                return ['code' => 500, 'msg' => "修改失败"];
            }
            return ['code' => 200, 'msg' => "修改成功"];
        } catch (Exception $exception) {
            Log::error("NewsService->create:" . $exception->getMessage());
            return ['code' => 500, 'msg' => $exception->getMessage()];
        }
    }

    /**
     * User: Yan
     * DateTime: 2023/3/7
     * @param $id
     * @return int
     * 删除
     */
    public function destroy($id): int
    {
        return RecommendData::destroy($id);
    }

}
