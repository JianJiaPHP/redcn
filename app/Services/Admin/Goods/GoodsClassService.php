<?php


namespace App\Services\Admin\Goods;


use App\Models\chat\Goods;
use App\Models\chat\GoodsClass;
use App\Models\chat\Subscribe;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class GoodsClassService
{
    /**
     * 列表
     * @param $data
     * @return LengthAwarePaginator
     */
    public function list($data): LengthAwarePaginator
    {
        $where = [];
        /** 订单号 */
        if (!empty($data['title'])) {
            $where[] = ['title', 'like', '%'.$data['title'] .'%'];
        }
        $data = GoodsClass::query()->with('functionType')->where($where)->orderBy('id', 'desc');

        return $data->paginate(request()->query('limit', 15));
    }

    /**
     * 修改
     * @param $id
     * @param $params
     * @return array
     * @Time 2023/5/19 14:12
     * @author sunsgne
     */
    public function update($id, $params): array
    {
        $saveData = [];
        if (!empty($params['title'])) {
            $saveData['title'] = $params['title'];
        }
        if (!empty($params['type'])) {
            $saveData['type'] = $params['type'];
        }
        if (!empty($params['function_type'])) {
            $saveData['function_type'] = $params['function_type'];
        }
        if (!empty($params['function_token'])) {
            $saveData['function_token'] = $params['function_token'];
        }
        if (!empty($params['function_url'])) {
            $saveData['function_url'] = $params['function_url'];
        }
        if (!empty($params['image'])) {
            $saveData['image'] = $params['image'];
        }
        if (!empty($params['introduce'])) {
            $saveData['introduce'] = $params['introduce'];
        }
        GoodsClass::query()->where(['id' => $id])->update($saveData);
        return ['code' => 200, 'msg' => "修改成功"];
    }

}
