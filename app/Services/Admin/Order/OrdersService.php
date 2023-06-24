<?php


namespace App\Services\Admin\Order;


use App\Models\chat\PayOrder;
use App\Models\News\News;
use DB;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Log;

class OrdersService
{
    /**
     * User: Yan
     * DateTime: 2023/3/7
     * @param $params
     * @return LengthAwarePaginator
     * 查询订单列表
     */
    public function list($params): LengthAwarePaginator
    {
        $where = [];
        /** 订单号 */
        if (!empty($params['order_no'])) {
            $where[] = ['order_no', '=', $params['order_no']];
        }
        /** 用户ID */
        if (!empty($params['user_id'])) {
            $where[] = ['user_id', '=', $params['user_id']];
        }
        /** 商品ID */
        if (!empty($params['goods_id'])) {
            $where[] = ['goods_id', '=', $params['goods_id']];
        }
        /** 支付状态 */
        if (!empty($params['pay_status'])) {
            $where[] = ['pay_status', '=', $params['pay_status']];
        }
        /** 支付类型 */
        if (!empty($params['pay_type'])) {
            $where[] = ['pay_type', '=', $params['pay_type']];
        }
        if (isset($params['created_at'][0]) && isset($params['created_at'][1])) {
            $where[] = ['created_at', '>=', $params['created_at'][0]];
            $where[] = ['created_at', '<=', $params['created_at'][1]];
        }

        $data = PayOrder::query()->with(['user' , 'goods'])->where($where)->orderBy('id', 'desc');

        return $data->paginate(request()->query('limit', 15));
    }


    /**
     * 修改订单
     * User: Yan
     * DateTime: 2023/3/7
     * @param $id
     * @param $params
     * @return array
     *
     */
    public function update($id, $params): array
    {
        PayOrder::query()->where(['id' => $id])->update([
            'remark'     => $params['remark'],
        ]);
        return ['code' => 200, 'msg' => "修改成功"];
    }

    /**
     * 删除订单
     * User: Yan
     * DateTime: 2023/3/7
     * @param $id
     * @return int
     * 删除
     */
    public function destroy($id): int
    {
        return PayOrder::destroy($id);
    }

}
