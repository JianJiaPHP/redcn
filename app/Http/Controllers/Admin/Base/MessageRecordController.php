<?php


namespace App\Http\Controllers\Admin\Base;


use App\Http\Controllers\Controller;
use App\Http\Requests\MessageRecordCreateRequests;
use App\Utils\Result;
use Exception;
use Illuminate\Http\JsonResponse;


class MessageRecordController extends Controller
{

    protected \App\Services\Admin\Base\MessageRecordService $messageRecordService;

    public function __construct(\App\Services\Admin\Base\MessageRecordService $messageRecordService)
    {
        $this->messageRecordService = $messageRecordService;
    }

    /**
     * User: Yan
     * DateTime: 2023/3/3
     * @return JsonResponse 消息记录列表
     * 消息记录列表
     */
    public function index(): JsonResponse
    {
        $params = request()->all();

        $data = $this->messageRecordService->list($params);

        return Result::success($data);
    }

    /**
     * User: Yan
     * DateTime: 2023/3/3
     * @return JsonResponse
     * 创建消息
     * @throws Exception
     */
    public function store(MessageRecordCreateRequests $requests): JsonResponse
    {
        $params = $requests->all();

        try {
            $data = $this->messageRecordService->sendMessage($params);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
        return Result::choose($data);
    }

    /**
     * User: Yan
     * DateTime: 2023/3/3
     * @param $id
     * @return JsonResponse 删除发布消息记录
     * 删除发布消息记录
     */
    public function destroy($id): JsonResponse
    {
        $res = $this->messageRecordService->destroy($id);

        return Result::choose($res);
    }

}
