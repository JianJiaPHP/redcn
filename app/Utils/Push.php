<?php


namespace App\Utils;

use App\Helpers\HttpHelper;
use GTAlert;
use GTAndroid;
use GTAps;
use GTClient;
use GTIos;
use GTNotification;
use GTPushChannel;
use GTPushMessage;
use GTPushRequest;
use GTThirdNotification;
use GTUps;

/**
 * 个推
 * Class Push
 * @package App\Utils
 */
class Push
{
    use HttpHelper;

    protected $appId;
    protected $appKey;
    protected $appSecret;
    protected $masterSecret;
    protected $xm;
    protected $oppo;
    protected $api;

    private $title;
    private $body;
    private $clickType = 'none';
    private $url;
    private $cid;
    private $requestId;

    public function __construct()
    {
        $pushConfig = config('push');
        $this->appId = $pushConfig['appId'];
        $this->appKey = $pushConfig['appKey'];
        $this->appSecret = $pushConfig['appSecret'];
        $this->masterSecret = $pushConfig['masterSecret'];
        $this->xm = $pushConfig['xm'];
        $this->oppo = $pushConfig['oppo'];
        $this->api = new GTClient("https://restapi.getui.com", $this->appKey, $this->appId, $this->masterSecret);
    }

    /**
     * @return mixed
     */
    public function getCid()
    {
        return $this->cid;
    }

    /**
     * @param mixed $cid
     */
    public function setCid($cid): void
    {
        $this->cid = $cid;
    }

    /**
     * @return mixed
     */
    public function getRequestId()
    {
        return $this->requestId;
    }

    /**
     * @param mixed $requestId
     */
    public function setRequestId($requestId): void
    {
        $this->requestId = $requestId;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $title
     */
    public function setTitle($title): void
    {
        $this->title = $title;
    }

    /**
     * @return mixed
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @param mixed $body
     */
    public function setBody($body): void
    {
        $this->body = $body;
    }

    /**
     * @return string
     */
    public function getClickType()
    {
        return $this->clickType;
    }

    /**
     * @param mixed $clickType
     */
    public function setClickType($clickType): void
    {
        $this->clickType = $clickType;
    }

    /**
     * @return mixed
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param mixed $url
     */
    public function setUrl($url): void
    {
        $this->url = $url;
    }

    /**
     * 推送消息
     * @return array
     * author II
     */
    public function pushToSingleByCid()
    {
        //设置推送参数
        $push = new GTPushRequest();
        $push->setRequestId($this->requestId);
        $push->setPushMessage($this->createMessage());
        $push->setCid($this->cid);
        // 设置厂商
        $push->setPushChannel($this->pushChannel());
        //处理返回结果
        $response = $this->api->pushApi()->pushToSingleByCid($push);
        if ($response["code"] == 0) {
            $res = [];
            foreach ($response['data'] as $k => $v) {
                $res['taskid'] = $k;
                foreach ($response['data'][$k] as $k1 => $v1) {
                    $res['status'] = $response['data'][$k][$k1];
                }
            }

            return $res;
        }
        return [];
    }

    /**
     * 创建消息
     * @return GTPushMessage
     * author II
     */
    private function createMessage()
    {
        $message = new GTPushMessage();
        $notify = new GTNotification();
        $notify->setTitle($this->title);
        $notify->setBody($this->body);
        //点击通知后续动作，目前支持以下后续动作:
        //1、intent：打开应用内特定页面url：打开网页地址。2、payload：自定义消息内容启动应用。3、payload_custom：自定义消息内容不启动应用。4、startapp：打开应用首页。5、none：纯通知，无后续动作
        $notify->setClickType($this->clickType);
        if ($this->clickType == GTThirdNotification::CLICK_TYPE_URL) {
            $notify->setUrl($this->url);
        }
        $message->setNotification($notify);
        return $message;
    }

    /**
     * 厂商通道
     * @return GTPushChannel
     * author II
     */
    private function pushChannel()
    {
        //厂商
        $pushChannel = new GTPushChannel();
        // 安卓通道
        $android = new GTAndroid();
        //android厂商通道推送消息内容
        $ups = new GTUps();

        $thirdNotification = new GTThirdNotification();
        $thirdNotification->setTitle($this->title);
        $thirdNotification->setBody($this->body);
        $thirdNotification->setClickType($this->clickType);
        if ($this->clickType == GTThirdNotification::CLICK_TYPE_URL) {
            $thirdNotification->setUrl($this->url);
        }
        $ups->setNotification($thirdNotification);
        $ups->addOption("ALL", "channel", 'Default');
        $ups->addOption("XM", "channel", $this->xm);
        $ups->addOption("OP", "channel", $this->oppo);
        $ups->addOption("HW", "/message/android/notification/style", 1);
        $ups->addOption("HW", "/message/android/notification/big_title", $this->title);
        $ups->addOption("HW", "/message/android/notification/big_body", $this->body);
        $android->setUps($ups);

        $pushChannel->setAndroid($android);

        // ios通道
        $ios = new GTIos();
        $aps = new GTAps();
        $alert = new GTAlert();
        $alert->setTitle($this->title);
        $alert->setBody($this->body);
        $aps->setAlert($alert);
        $ios->setAps($aps);

        $pushChannel->setIos($ios);

        return $pushChannel;
    }

    /**
     * 全部推送
     * @return mixed
     * author II
     */
    public function pushAll()
    {
        $push = new GTPushRequest();
        $push->setRequestId($this->requestId);
        $push->setPushMessage($this->createMessage());
        // 设置厂商
        $push->setPushChannel($this->pushChannel());

        return $this->api->pushApi()->pushAll($push);
    }


}
