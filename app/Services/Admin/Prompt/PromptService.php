<?php


namespace App\Services\Admin\Prompt;


use App\Models\News\News;
use App\Models\Prompt\Prompt;
use DB;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Log;

class PromptService
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
        if (!empty($params['name'])) {
            $where[] = ['title', 'like', "%" . $params['name'] . "%"];
        }
        if (!empty($params['class_id'])) {
            $where[] = ['class_id', '=', $params['class_id']];
        }
        if (isset($params['created_at'][0]) && isset($params['created_at'][1])) {
            $where[] = ['created_at', '>=', $params['created_at'][0]];
            $where[] = ['created_at', '<=', $params['created_at'][1]];
        }

        $data = Prompt::query()->with('class')->where($where)->orderBy('id', 'desc');

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

            $add = Prompt::query()->create(
                [
                    'class_id' => $params['class_id'],
                    'title'    => $params['title'],
                    'content'  => $params['content'],
                    'subtitle' => $params['subtitle'],
                    'zan_num'  => $params['zan_num'],
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
        Prompt::query()->where(['id' => $id])->update([
            'class_id' => $params['class_id'],
            'title'    => $params['title'],
            'content'  => $params['content'],
            'subtitle' => $params['subtitle'],
            'zan_num'  => $params['zan_num'],
        ]);
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
        return Prompt::destroy($id);
    }

}
