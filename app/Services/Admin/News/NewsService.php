<?php


namespace App\Services\Admin\News;


use App\Models\News\News;
use DB;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Log;

class NewsService
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
        if (!empty($params['class_id'])) {
            $where[] = ['class_id', '=', $params['class_id']];
        }
        if (!empty($params['key_name'])) {
            $where[] = ['key_name', '=', $params['key_name']];
        }
        if (isset($params['created_at'][0]) && isset($params['created_at'][1])) {
            $where[] = ['created_at', '>=', $params['created_at'][0]];
            $where[] = ['created_at', '<=', $params['created_at'][1]];
        }

        $data = News::query()->with('class_info')->where($where)->orderBy('created_at', 'desc');

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
            DB::beginTransaction();
            $is = News::query()->where('key_name', $params['key_name'])->exists();
            if ($is) {
                return ['code' => 500, 'msg' => "key值已存在"];
            }
            if (empty($params['address'])){
                return ['code' => 500, 'msg' => "封面图不能为空"];
            }
            $add = News::query()->create(
                [
                    'class_id'     => $params['class_id'],
                    'key_name'     => $params['key_name'],
                    'name'         => $params['name'],
                    'content_text' => $params['content_text'],
                    'address'      => $params['address']
                ]
            );
            if (!$add) {
                DB::rollBack();
                return ['code' => 500, 'msg' => "添加失败"];
            }
//            $add_address = News::query()->where('id', $add['id'])->update([
//                'address' => env('URL_H5_ADDRESS') . "?id=" . $add['id']
//            ]);
//            if (!$add_address) {
//                DB::rollBack();
//                return ['code' => 500, 'msg' => "添加失败"];
//            }
            DB::commit();
            return ['code' => 200, 'msg' => "添加成功"];
        } catch (Exception $exception) {
            Log::error("NewsService->create:" . $exception->getMessage());
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
        if (empty($params['address'])){
            return ['code' => 500, 'msg' => "封面图不能为空"];
        }
        News::query()->where(['id' => $id])->update([
            'class_id'     => $params['class_id'],
            'key_name'     => $params['key_name'],
            'name'         => $params['name'],
            'content_text' => $params['content_text'],
            'address'      => $params['address']
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
        return News::destroy($id);
    }

}
