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
     */
    public function upload(Request $request): JsonResponse
    {
        $file = $request->file('file');
        if (!$file){
            return Result::fail('请上传文件');
        }
        # 文件大小不能超过200M
        if ($file->getSize() > 200 * 1024 * 1024) {
            return Result::fail('文件大小不能超过3M');
        }
//        $path = Upload::upload($file, 1, auth('api')->id() ?? 0);
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $path = $file->store('uploads'); // 存储文件到 storage/app/uploads 目录下
            return Result::success([
                'path' => \Storage::url($path),
                'app_url' => env('APP_URL')
            ]);
        }
        return Result::fail('上传失败');

    }


}

