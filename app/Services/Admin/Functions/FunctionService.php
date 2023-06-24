<?php


namespace App\Services\Admin\Functions;


use App\Models\chat\Functions;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class FunctionService
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
        if (!empty($params['title'])) {
            $where[] = ['title', 'like', '%'.$params['title']];
        }

        if (isset($params['created_at'][0]) && isset($params['created_at'][1])) {
            $where[] = ['created_at', '>=', $params['created_at'][0]];
            $where[] = ['created_at', '<=', $params['created_at'][1]];
        }

        $data = Functions::query()->where($where)->orderBy('id', 'desc');

        return $data->paginate(request()->query('limit', 15));
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
        Functions::query()->where(['id' => $id])->update($params);
        return ['code' => 200, 'msg' => "修改成功"];
    }


}
