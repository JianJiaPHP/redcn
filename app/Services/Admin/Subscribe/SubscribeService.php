<?php


namespace App\Services\Admin\Subscribe;


use App\Models\chat\Subscribe;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class SubscribeService
{
    /**
     * 列表
     * @param $data
     * @return LengthAwarePaginator
     */
    public function list($data): LengthAwarePaginator
    {
        $where = [];
       if (!empty($data['user_id'])){
           $where['user_id'] = $data['user_id'];
       }

        if (!empty($data['goods_id'])){
            $where['goods_id'] = $data['goods_id'];
        }

        if (isset($params['created_at'][0]) && isset($params['created_at'][1])) {
            $where[] = ['created_at', '>=', $params['created_at'][0]];
            $where[] = ['created_at', '<=', $params['created_at'][1]];
        }

        $data = Subscribe::query()->with(['user' , 'goods'])->where($where)->orderBy('id' , 'desc');

        return $data->paginate(request()->query('limit', 15));
    }

}
