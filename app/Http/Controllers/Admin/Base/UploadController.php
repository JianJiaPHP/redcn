<?php


namespace App\Http\Controllers\Admin\Base;


use App\Http\Controllers\Controller;
use App\Utils\Result;
use App\Utils\Upload;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UploadController extends Controller
{

    /**
     * 上传文件
     * @param Request $request
     * @return JsonResponse
     * author II
     */
    public function upload(Request $request): JsonResponse
    {
        $file = $request->file('file');
        # 文件大小不能超过200M
        if ($file->getSize() > 200 * 1024 * 1024) {
            return Result::fail('文件大小不能超过200M');
        }
        $path = Upload::upload($file, 1, auth('api')->id() ?? 0);

        return Result::success([
            'path' => $path
        ]);
    }

    /**
     * User: Yan
     * DateTime: 2023/3/7
     * @param Request $request
     * @return JsonResponse
     * 上传视频 截取视频分辨率  截取第一帧
     */
    public function uploadVideo(Request $request): JsonResponse
    {
        $file = $request->file('file');
        # 判断文件是否是mp4格式
        if ($file->getClientOriginalExtension() != 'mp4') {
            return Result::fail('请上传mp4格式的视频');
        }
        # 文件大小不能超过200M
        if ($file->getSize() > 200 * 1024 * 1024) {
            return Result::fail('文件大小不能超过200M');
        }
        try {
            $path = Upload::uploadVideo($file, 1, auth('api')->id() ?? 0);
        } catch (\Exception $e) {
            return Result::fail($e->getMessage());
        }

        return Result::success([
            'path' => $path
        ]);

    }
}
