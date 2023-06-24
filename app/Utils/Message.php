<?php


namespace App\Utils;


class Message
{
    /**
     * User: Yan
     * DateTime: 2023/3/7
     * @param $title
     * @param $content
     * @param $user_id
     * 添加消息通知
     * @param int $type
     */
    public static function sendMessage($title, $content, $user_id, int $type = 1)
    {

        \App\Models\Base\Message::query()->create([
            'title'   => $title,
            'content' => $content,
            'user_id' => $user_id,
            'is_read' => 0,
            'type'    => $type
        ]);
    }

}
