<?php


namespace App\Services\Admin\Config;


use App\Models\Base\Banner;
use App\Models\Base\Config;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ConfigService
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
        if (!empty($params['group'])) {
            $where[] = ['group', '=', $params['group']];
        }
        if (!empty($params['key'])) {
            $where[] = ['key', '=', $params['key']];
        }
        if (isset($params['created_at'][0]) && isset($params['created_at'][1])) {
            $where[] = ['created_at', '>=', $params['created_at'][0]];
            $where[] = ['created_at', '<=', $params['created_at'][1]];
        }

        $data = Config::query()->where($where)->orderBy('id', 'desc');

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

            $add = Config::query()->create($params);

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
        Config::query()->where(['id' => $id])->update($params);
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
        return Config::destroy($id);
    }

}