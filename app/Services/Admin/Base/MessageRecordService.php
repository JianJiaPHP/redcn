<?php


namespace App\Services\Admin\Base;


use App\Models\Base\Message;
use App\Models\Base\MessageLog;
use App\Models\User\Users;
use Carbon\Carbon;
use DB;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class MessageRecordService
{

    /**
     * User: Yan
     * DateTime: 2023/3/3
     * @param $params
     * @return LengthAwarePaginator 发送记录表
     * 发送记录表
     */
    public function list($params): LengthAwarePaginator
    {
        $where = [];
        # 创建时间
        if (!empty($params['created_at'])) {
            $where[] = ['created_at', '>=', $params['created_at']];
        }
        # 消息类型
        if (!empty($params['type'])) {
            $where[] = ['type', '=', $params['type']];
        }
        # 消息标题
        if (!empty($params['title'])) {
            $where[] = ['title', 'like', "%" . $params['title'] . "%"];
        }
        # 用户id
        if (!empty($params['user_id'])) {
            $where[] = ['users','like', "%" . $params['user_id'] . "%"];
        }
        return MessageLog::query()->with('operator')->where($where)
            ->orderBy('id', 'desc')
            ->paginate(request()->query('limit', 15));
    }

    /**
     * User: Yan
     * DateTime: 2023/3/3
     * @return bool
     * 创建发送消息
     * @throws Exception
     */
    public function sendMessage($params): bool
    {
        DB::beginTransaction();
        try {
            # 创建发送记录
            $resLog = MessageLog::query()->create([
                'type'        => $params['type'],
                'title'       => $params['title'],
                'content'     => $params['content'],
                'users'       => $params['users'],
                'operator_id' => auth('api')->id()
            ]);
            if (!$resLog) {
                DB::rollBack();
                throw new Exception("发送失败！");
            }

            # 获取需要发给那些人
            if ($params['users'] == 'all') {
                $users = Users::query()->where(['is_ban' => 0])->pluck('id');
            } else {
                $users = Users::query()->whereIn('id', explode(',', $params['users']))->pluck('id');
            }
            # 发送消息
            $messageArr = [];
            foreach ($users as $v) {
                $messageArr[] = [
                    'title'      => $params['title'],
                    'content'    => $params['content'],
                    'user_id'    => $v,
                    'is_read'    => 0,
                    'type'       => $params['type'],
                    'image'      => $params['image']??'',
                    'text'       => $params['text']??'',
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ];
            }
            Message::query()->insert($messageArr);
            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage());
        }

    }

    /**
     * User: Yan
     * DateTime: 2023/3/3
     * @param $id
     * @return int
     * 删除发送消息记录
     */
    public function destroy($id): int
    {
        return MessageLog::destroy($id);
    }
}
