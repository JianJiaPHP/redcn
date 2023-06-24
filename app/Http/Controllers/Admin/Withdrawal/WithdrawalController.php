<?php


namespace App\Http\Controllers\Admin\Withdrawal;


use App\Http\Controllers\Controller;
use App\Services\Admin\Withdrawal\WithdrawalService;
use App\Utils\Result;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use function PHPUnit\Framework\isJson;

class WithdrawalController extends Controller
{
    private WithdrawalService $withdrawalService;

    /**
     * WithdrawalController constructor.
     */
    public function __construct(WithdrawalService $withdrawalService)
    {
        $this->withdrawalService = $withdrawalService;
    }


    /**
     * 提现列表
     * @param Request $request
     * @return JsonResponse
     * author Yan
     */
    public function index(Request $request): JsonResponse
    {
        $data = $request->all();

        $list = $this->withdrawalService->list($data);

        return Result::success($list);
    }

    /**
     * 获取通过弹窗所需信息
     * @param Request $request
     * @return JsonResponse
     * @author Yan
     */
    public function getSuccessWindowsInfo(Request $request): JsonResponse
    {
        $withdrawal_id = $request->query('id', 0);
        $list = $this->withdrawalService->getSuccessWindowsInfo($withdrawal_id);
        return Result::success($list);
    }


    /**
     * 获取所有支付方式
     * @return JsonResponse
     */
    public function getPayList(): JsonResponse
    {
        $list = $this->withdrawalService->getPayList();
        return Result::success($list);
    }

    /**
     * 变更订单状态
     * @param $id
     * @return JsonResponse
     * @author Yan
     */
    public function changeWithdrawalStatus($id): JsonResponse
    {
        $data = \request()->all();
        $data['id'] = $id;
        try {
            $result = $this->withdrawalService->changeWithdrawalStatus($data);
            if ($result['code'] != 200) {
                return Result::fail($result['msg'] ?? '');
            }
            return Result::success($result['msg']);
        } catch (Exception $e) {
            return Result::fail($e->getMessage());
        }
    }

    /**
     * User: Yan
     * DateTime: 2023/3/3
     * @return JsonResponse
     * 获取提现设置配置项
     */
    public function getWithdrawalConfig(): JsonResponse
    {
        $list = $this->withdrawalService->getWithdrawalConfig();
        return Result::success($list);
    }

    /**
     * User: Yan
     * DateTime: 2023/3/3
     * @return JsonResponse
     * 修改提现设置配置项
     */
    public function updateWithdrawalConfig(): JsonResponse
    {
        $data = request()->all();

        $data = !isJson($data) ? json_decode($data, true) : $data;
        if (is_string($data)) {
            $data = json_decode($data, true);
        }
        $result = $this->withdrawalService->updateWithdrawalConfig($data);
        if (!$result) {
            return Result::fail('修改失败');
        }
        return Result::success('修改成功');
    }


}
