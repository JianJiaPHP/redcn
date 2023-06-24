<?php


namespace App\Services\Admin\Feed;


use App\Models\Feed\ApplyStore;
use App\Models\Feed\Feedback;
use App\Models\Feed\UserFeedback;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class FeedService
{

    /**
     * User: Yan
     * DateTime: 2023/3/7
     * @param $params
     * @return LengthAwarePaginator
     * 意见反馈分类 列表
     */
    public function list($params): LengthAwarePaginator
    {
        $where = [];
        //查询分类
        if (!empty($params['title'])) {
            $where[] = ['title', 'like', "%" . $params['title'] . "%"];
        }
        $data = Feedback::query()->where($where)->orderBy('id', 'desc');

        return $data->paginate(request()->query('limit', 15));
    }


    /**
     * User: Yan
     * DateTime: 2023/2/28
     * @param $params
     * @return Builder|Model
     * 意见反馈分类新增
     */
    public function create($params)
    {
        return Feedback::query()->create($params);
    }

    /**
     * User: Yan
     * DateTime: 2023/2/28
     * @param $id
     * @param $params
     * @return int
     * 意见反馈分类 更新
     */
    public function update($id, $params): int
    {
        return Feedback::query()->where(['id' => $id])->update($params);
    }


    /**
     * User: Yan
     * DateTime: 2023/2/28
     * @param $id
     * @return int
     * 意见反馈分类 删除
     */
    public function destroy($id): int
    {
        return Feedback::destroy($id);
    }

    /**
     * User: Yan
     * DateTime: 2023/2/28
     * @return Builder[]|Collection 意见反馈分类 所有
     * 意见反馈分类 所有
     */
    public function getAll()
    {
        return Feedback::query()->get();
    }


    /**
     * User: Yan
     * DateTime: 2023/2/28
     * @param $params
     * @return LengthAwarePaginator 意见反馈 列表
     * 意见反馈 列表
     */
    public function feedList($params): LengthAwarePaginator
    {
        $whereUser = [];
        $where = [];
        //查询分类
        if (!empty($params['feedback_id'])) {
            $where['feedback_id'] = $params['feedback_id'];
        }
        if (!empty($params['user_id'])) {
            $whereUser[] = ['user_id', 'like', "%" . $params['user_id'] . "%"];
        }
        $list = UserFeedback::query()->with(['userInfo', 'feedbackInfo'])
            ->where($where);
        if ($whereUser) {
            $list = $list->whereHas('userInfo', function ($query) use ($whereUser) {
                $query->where($whereUser);
            });
        }
        $list = $list->orderBy('id', 'desc')
            ->paginate(request()->query('limit', 15));
        foreach ($list as &$v) {
            $v['image'] = explode(',', $v['image'] ?? []);
        }
        return $list;
    }


    /**
     * User: Yan
     * DateTime: 2023/2/28
     * @param $params
     * @return LengthAwarePaginator 商务合作列表
     * 商务合作列表
     */
    public function appleStore($params): LengthAwarePaginator
    {
        $where = [];
        //查询分类
        if (!empty($params['real_name'])) {
            $where[] = ['real_name', 'like', "%" . $params['real_name'] . "%"];
        }
        if (!empty($params['name'])) {
            $where[] = ['name', 'like', "%" . $params['name'] . "%"];
        }
        $list = ApplyStore::query()->with(['userInfo'])
            ->where($where);
        return $list->orderBy('id', 'desc')
            ->paginate(request()->query('limit', 15));
    }

}
