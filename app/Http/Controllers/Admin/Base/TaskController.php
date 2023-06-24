<?php


namespace App\Http\Controllers\Admin\Base;


use App\Http\Controllers\Controller;
use App\Models\Base\Task;
use App\Utils\Result;
use Illuminate\Http\JsonResponse;

# AI任务列表
class TaskController extends Controller
{

    protected $task;

    public function __construct(Task $task)
    {
        $this->task = $task;
    }

    /**
     * User: Yan
     * DateTime: 2023/3/7
     * @return JsonResponse
     * AI任务列表
     */
    public function index(): JsonResponse
    {
        $params = request()->all();
        $where = [];
        if (!empty($params['status'])) {
            $where['status'] = $params['status'];
        }
        $data = Task::query()->where($where)->orderBy('id', 'desc')->paginate(`request()->query('limit', 15)`);
        return Result::success($data);
    }

}
