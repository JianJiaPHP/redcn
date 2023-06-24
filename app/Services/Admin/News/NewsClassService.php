<?php


namespace App\Services\Admin\News;


use App\Models\News\NewsClass;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class NewsClassService
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
            $where[] = ['name', 'like', "%" . $params['name'] . "%"];
        }
        $data = NewsClass::query()->where($where)->orderBy('id', 'desc');

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
        return NewsClass::query()->create($params);
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
        return NewsClass::query()->where(['id' => $id])->update($params);
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
        return NewsClass::destroy($id);
    }

}
