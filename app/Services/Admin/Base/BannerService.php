<?php

namespace App\Services\Admin\Base;

use App\Models\Base\Banner;
use DB;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Log;

/**
 * 小程序Banner
 */
class BannerService
{
    /**
     * User: Yan
     * DateTime: 2023/3/7
     * @return LengthAwarePaginator 查询分类列表
     * 查询分类列表
     */
    public function list($params): LengthAwarePaginator
    {
        $where = [];
        if (!empty($params['classify'])) {
            $where[] = ['classify', '=', $params['classify']];
        }
        # 开始时间
        if (!empty($params['created_at'])){
            if (!empty($params['created_at'][0]&&!empty($params['created_at'][1]))) {
                $where[] = ['start_time', '>=', $params['created_at'][0]];
                $where[] = ['start_time', '<=', $params['created_at'][1]];
            }
        }
        return Banner::query()->where($where)->orderBy('sort')->paginate(request()->query('limit', 15));
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
        try {
            $bannerRes = Banner::query()->where(['id' => $id])->update([
                'cover'     => $params['cover'],
                'classify'  => auth('api')->id() == 1 ? $params['classify'] : 4,
                'store_id'  => auth('api')->id() == 1 ? 0 : auth('api')->id(),
                'sort'      => $params['sort'],
                'link'      => $params['link']??'',
                'start_time'=> $params['start_time'],
                'end_time'  => $params['end_time'],
                'is_hidden' => $params['is_hidden'],
            ]);
            if (!$bannerRes) {
                throw new Exception("修改失败");
            }
            return ['code' => 200, 'msg' => "修改成功"];
        } catch (Exception $e) {
            Log::error("BannerService->update:" . $e->getMessage());
            return ['code' => 500, 'msg' => $e->getMessage()];
        }

    }

    /**
     * User: Yan
     * DateTime: 2023/3/7
     * @param $params
     * @return array
     * @throws Exception
     */
    public function create($params): array
    {
        try {
            DB::beginTransaction();
            $add = Banner::query()->create(
                [
                    'cover'     => $params['cover'],
                    'classify'  => auth('api')->id() == 1 ? $params['classify'] : 4,
                    'store_id'  => auth('api')->id() == 1 ? 0 : auth('api')->id(),
                    'sort'      => $params['sort'],
                    'link'      => $params['link']??'',
                    'start_time'=> $params['start_time'],
                    'end_time'  => $params['end_time'],
                    'is_hidden' => (int)$params['is_hidden'],
                ]
            );
            if (!$add) {
                DB::rollBack();
                return ['code' => 500, 'msg' => "添加失败"];
            }
            DB::commit();
            return ['code' => 200, 'msg' => "添加成功"];
        } catch (Exception $exception) {
            Log::error("BannerService->create:" . $exception->getMessage());
            return ['code' => 500, 'msg' => $exception->getMessage()];
        }

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
        return Banner::destroy($id);
    }

}
