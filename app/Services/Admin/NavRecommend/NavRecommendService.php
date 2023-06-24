<?php


namespace App\Services\Admin\NavRecommend;


use App\Models\Base\Banner;
use App\Models\News\RecommendData;
use App\Utils\Result;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;

class NavRecommendService
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
        if (!empty($params['level'])) {
            $where[] = ['level', '=', $params['level']];
        }
        if (!empty($params['p_id'])) {
            $where[] = ['p_id', '=', $params['p_id']];
        }
        if (isset($params['created_at'][0]) && isset($params['created_at'][1])) {
            $where[] = ['created_at', '>=', $params['created_at'][0]];
            $where[] = ['created_at', '<=', $params['created_at'][1]];
        }

        $data = RecommendData::query()->where($where)->orderBy('id', 'desc');

        return $data->paginate(request()->query('limit', 15));
    }

    public function dataTree(): array
    {
        $data = RecommendData::query()->where(['p_id' => 0, 'is_open' => 1, 'level' => 1])->select(['*'])->get();
        foreach ($data as $key => $value) {
            $data[$key]['children'] = RecommendData::query()->where(['p_id' => $value['id'], 'level' => 2])->select(['*'])->get();
            foreach ($data[$key]['children'] as $k => $v) {
                $data[$key]['children'][$k]['children'] = RecommendData::query()->where(['p_id' => $v['id'], 'level' => 3])->select(['*'])->get();
            }
        }
        return $data->toArray();
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

            $add = RecommendData::query()->create($params);

            return ['code' => 200, 'msg' => "添加成功"];
        } catch (Exception $exception) {
            return ['code' => 500, 'msg' => $exception->getMessage()];
        }

    }


    /**
     * User: Yan
     * DateTime: 2023/3/7
     * @param $id
     * @param $params
     * @return array 修改分类
     * 修改分类
     */
    public function update($id, $params): array
    {
        RecommendData::query()->where(['id' => $id])->update($params);
        return ['code' => 200, 'msg' => "修改成功"];
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
