<?php


namespace App\Utils;


use App\Models\Base\Files;
use Carbon\Carbon;
use Exception;
use FFMpeg\Coordinate\TimeCode;
use Illuminate\Http\UploadedFile;
use Log;
use OSS\Core\OssException;
use OSS\OssClient;
use Storage;
use Str;
use ZipArchive;

class Upload
{

    private static $bucket;
    private static $hua_bucket;

    /**
     * 创建zip
     * @param $files
     * @param $zipName
     * author Yan
     */
    public static function zip($files, $zipName)
    {
        // 初始化zip
        $zip = new ZipArchive();
        // zip名称
        $fileName = $zipName . '.zip';
        // 打开zip
        $zip->open($fileName, ZIPARCHIVE::CREATE | ZIPARCHIVE::OVERWRITE);
        // 写入文件
        foreach ($files as $v) {
            $ossClient = self::createClient();
            $content = $ossClient->getObject(self::$bucket, $v);
            $zip->addFromString($v, $content);
        }
        // 关闭zip
        $zip->close();
        // 向浏览器输出zip
        header("Content-Type: application/zip");
        header("Content-Length: " . filesize($fileName));
        header("Content-Disposition: attachment; filename=\"a_zip_file.zip\"");
        readfile($fileName);
        // 删除zip
        unlink($fileName);
    }

    /**
     * 创建阿里云oss
     * @return OssClient|null
     * author Yan
     */
    private static function createClient(): ?OssClient
    {
        $ossConfig = config('aliyun.oss');
        $accessKeyId = $ossConfig['accessKeyId'];
        $accessKeySecret = $ossConfig['accessKeySecret'];
        $endpoint = $ossConfig['endpoint'];
        self::$bucket = $ossConfig['bucket'];
        try {
            return new OssClient($accessKeyId, $accessKeySecret, $endpoint);
        } catch (OssException $e) {
            Log::info("阿里云oss异常" . $e->getMessage());
        }
        return null;
    }

    /**
     * 文件上传
     * @param UploadedFile $file
     * @param $type //上传者类型(0=用户 1=管理员)
     * @param $id // 上传者ID
     * @return mixed|string
     * author Yan
     */
    public static function upload(UploadedFile $file, $type, $id)
    {
        // 文件原始名称
        $originalName = $file->getClientOriginalName();
        // 文件大小（字节数)
        $size = $file->getSize();
        // 查看文件是否已经上传
//        $has = Files::query()->where(['original_name' => $originalName, 'file_size' => $size])->first();
//        if ($has) {
//            return $has['path'];
//        }
        // 临时路径
        $realPath = $file->getRealPath();
        // 后缀
        $extension = $file->getClientOriginalExtension();
        // 存入路径
        $object = date('Y-m-d/') . str_replace('-', "", Str::uuid()) . '.' . $extension;
        try {
            $ossClient = self::createClient();
            // 阿里云上传
            $result = $ossClient->uploadFile(self::$bucket, $object, $realPath);
            if (!$result) {
                return '';
            }
            Files::query()->create([
                'file_size'     => $size,
                'original_name' => $originalName,
                'path'          => $result['oss-request-url'],
                'object'        => $object,
                'uploader_id'   => $id,
                'uploader_type' => $type,
                'upload_at'     => Carbon::now()->toDateTimeString(),
            ]);
            return $result['oss-request-url'];

        } catch (OssException $e) {
            Log::info("上传文件出错" . $e->getMessage());
            return '';
        }
    }

    /**
     * 删除文件
     * @param string|array $object
     * @return null
     * author Yan
     */
    public static function deleteFile($object)
    {
        $ossClient = self::createClient();
        if (is_array($object)) {
            return $ossClient->deleteObjects(self::$bucket, $object);
        }
        return $ossClient->deleteObject(self::$bucket, $object);
    }

    /**
     * 华为-OBS-上传图片 视频/获取分辨率/截帧
     * @param UploadedFile $file
     * @param $type
     * @param $id
     * @return array|string
     * @throws Exception
     */
    public static function uploadVideo(UploadedFile $file, $type, $id)
    {
        // 文件原始名称
        $originalName = $file->getClientOriginalName();
        // 文件大小（字节数)
        $size = $file->getSize();
//        // 查看文件是否已经上传
//        $has = Files::query()->where(['original_name' => $originalName, 'file_size' => $size])->first();
//        if ($has) {
//            return $has['path'];
//        }
        // 临时路径
        $realPath = $file->getRealPath();
        // 后缀
        $extension = $file->getClientOriginalExtension();
        //拼接视频分辨率
        $size_data = self::getVideoInfo($realPath);
        if ($size_data) {
            $object = date('Y-m-d/') . str_replace('-', "", Str::uuid()) . 's_' . $size_data['width'] . 'x' . $size_data['height'] . '.' . $extension;
        } else {
            $object = date('Y-m-d/') . str_replace('-', "", Str::uuid()) . '.' . $extension;
        }
        try {
            //截取视频第一帧封面
            $cover = self::getCover($realPath, 1);
        } catch (Exception $e) {
            throw new Exception("视频截帧失败");
        }
        $cover_img = '';
        if ($cover) {
            $objectCover = date('Y-m-d/') . str_replace('-', "", Str::uuid()) . '.' . 'png';
            // 存入路径
            try {
                // 创建ObsClient实例
                $disk = Storage::disk('obs');
                $result = $disk->put($objectCover, file_get_contents(storage_path($cover)));

                if ($result) {
                    $cover_img = $disk->url($objectCover);
                } else {
                    return ['url' => '', 'cover' => ''];
                }
            } catch (OssException $e) {
                Log::info("上传文件出错" . $e->getMessage());
                return '';
            }
        }
        // 存入路径
        try {
            $disk = Storage::disk('obs');
            $result = $disk->put($object, file_get_contents($realPath));
            if (!$result) {
                return ['url' => '', 'cover' => ''];
            }
            $resultUrl = $disk->url($object);

            Files::query()->create([
                'file_size'     => $size,
                'original_name' => $originalName,
                'path'          => $resultUrl,
                'object'        => $object,
                'uploader_id'   => $id,
                'uploader_type' => $type,
                'cover'         => $cover_img,
                'upload_at'     => Carbon::now()->toDateTimeString(),
            ]);
            return ['url' => $resultUrl, 'cover' => $cover_img];

        } catch (OssException $e) {
            Log::info("上传文件出错" . $e->getMessage());
            return '';
        }

    }

    public static function getVideoInfo($streamPath): array
    {
        $ffprobe = app('ffprobe');
        $stream = $ffprobe->streams($streamPath)->videos()->first();
        return $stream ? $stream->all() : [];
    }

    // 获取视频信息

    public static function getCover($streamPath, $fromSecond): string
    {
        $ffmpeg = app('ffmpeg');
        $video = $ffmpeg->open($streamPath);
        $frame = $video->frame(TimeCode::fromSeconds($fromSecond)); //提取第几秒的图像
        $fileName = 'video/' . Str::random(12) . '.jpg';
        if (!is_dir(storage_path("video"))) {
            mkdir(storage_path("video"), 0777);
        }
        $frame->save(storage_path($fileName));
        return $fileName;
    }
}
