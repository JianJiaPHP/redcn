<?php


namespace App\Services\Admin\Goods;


use App\Models\chat\Goods;
use App\Models\chat\Subscribe;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class GoodsService
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
        $data = Goods::query()->with('goodsClass')->where($where)->orderBy('id', 'desc');

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
        if (!empty($params['image'])) {
            $saveData['image'] = $params['image'];
        }
        if (!empty($params['token_num'])) {
            $saveData['token_num'] = $params['token_num'];
        }
        if (!empty($params['amount'])) {
            $saveData['amount'] = $params['amount'];
        }
        if (!empty($params['type'])) {
            $saveData['type'] = $params['type'];
        }
        if (!empty($params['introduce'])) {
            $saveData['introduce'] = $params['introduce'];
        }
        if (!empty($params['valid_day'])) {
            $saveData['valid_day'] = $params['valid_day'];
        }
        if (!empty($params['text'])) {
            $saveData['text'] = $params['text'];
        }
        if (!empty($params['function_url'])) {
            $saveData['function_url'] = $params['function_url'];
        }
        if (!empty($params['function_type'])) {
            $saveData['function_type'] = $params['function_type'];
        }
        if (!empty($params['function_token'])) {
            $saveData['function_token'] = $params['function_token'];
        }
        if (!empty($params['class_id'])) {
            $saveData['class_id'] = $params['class_id'];
        }

        if (isset($params['is_pay'])) {
            $saveData['is_pay'] = $params['is_pay'];
        }
        if (isset($params['is_open'])) {
            $saveData['is_open'] = $params['is_open'];
        }

        Goods::query()->where(['id' => $id])->update($saveData);
        return ['code' => 200, 'msg' => "修改成功"];
    }

    /**
     * 商品删除
     * @param $id
     * @return int
     * @Time 2023/6/2 15:42
     * @author sunsgne
     */
    public function destroy($id): int
    {
        return Goods::destroy($id);
    }

    /**
     * 新增
     * @param array $data
     * @return int
     * @Time 2023/6/2 15:43
     * @author sunsgne
     */
    public function add(array $data): int
    {
        $data['created_at'] = $time = Carbon::now()->toDateTimeString();
        $data['updated_at'] = $time ;
        return Goods::query()->insertGetId($data);
    }

}
