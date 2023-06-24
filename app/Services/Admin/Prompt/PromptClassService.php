<?php


namespace App\Services\Admin\Prompt;


use App\Models\News\NewsClass;
use App\Models\Prompt\PromptClass;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class PromptClassService
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

        if (!empty($params['type'])) {
            $where[] = ['type', '=',  $params['type'] ];
        }
        $data = PromptClass::query()->where($where)->orderBy('id', 'desc');

        return $data->paginate(request()->query('limit', 15));
    }


    /**
     * User: Yan
     * DateTime: 2023/3/7
     * @param $params
     * @return Builder|Model
     * 添加分类
     */
    public function create($params)
    {
        return PromptClass::query()->create($params);
    }


    /**
     * User: Yan
     * DateTime: 2023/3/7
     * @param $id
     * @param $params
     * @return int
     * 修改分类
     */
    public function update($id, $params): int
    {
        return PromptClass::query()->where(['id' => $id])->update($params);
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
        return PromptClass::destroy($id);
    }

}
