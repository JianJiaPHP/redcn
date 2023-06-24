<?php


namespace App\Services\Admin\Prompt;


use App\Models\News\RecommendData;
use App\Models\Prompt\Prompt;
use App\Models\Prompt\PromptMidJourney;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class MidJourneyPromptService
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
        //查询分类名
        if (!empty($params['title'])) {
            $where[] = ['title', 'like', "%" . $params['title'] . "%"];
        }
        if (!empty($params['class_id'])) {
            $where[] = ['class_id', '=', $params['class_id']];
        }
        if (isset($params['created_at'][0]) && isset($params['created_at'][1])) {
            $where[] = ['created_at', '>=', $params['created_at'][0]];
            $where[] = ['created_at', '<=', $params['created_at'][1]];
        }

        $data = PromptMidJourney::query()->with('class')->where($where)->orderBy('id', 'desc');

        return $data->paginate(request()->query('limit', 15));
    }

    /**
     * @return array
     * @Time 2023/5/24 17:23
     * @author sunsgne
     */
    public function dataTree(): array
    {
        $data = PromptMidJourney::query()->where(['p_id' => 0, 'level' => 1])->select(['*'])->get();
        foreach ($data as $key => $value) {
            $data[$key]['children'] = PromptMidJourney::query()->where(['p_id' => $value['id'], 'level' => 2])->select(['*'])->get();
            foreach ($data[$key]['children'] as $k => $v) {
                $data[$key]['children'][$k]['children'] = PromptMidJourney::query()->where(['p_id' => $v['id'], 'level' => 3])->select(['*'])->get();
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

            $add = PromptMidJourney::query()->create(
                $params + [
                    'created_at' => $time = Carbon::now()->toDateTimeString(),
                    'updated_at' => $time
                ]
            );

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
        PromptMidJourney::query()->where(['id' => $id])->update($params);
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
        return PromptMidJourney::destroy($id);
    }

}
