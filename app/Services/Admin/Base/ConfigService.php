<?php


namespace App\Services\Admin\Base;


use App\Models\Config;

class ConfigService
{
    /**
     * 根据key获取value
     * @param string $key
     * @return string
     * author II
     */
    public function getOne(string $key): string
    {
        $data = $this->getAll();
        return $data[$key] ?? '';
    }

    /**
     * 获取所有配置
     * @return mixed
     * author II
     */
    public function getAll(): array
    {
        return (new Config())->getAll();
    }

    /**
     * 后台配置
     * @return array
     * author Yan
     */
    public function list(): array
    {
        $config = Config::getByWhere(['admin', 'proportion', 'withdrawal', 'douyin_clerk', 'service', 'system', 'customer_service']);
//        $data = [];
//        foreach ($config as $v) {
//            $data[$v['key']] = [
//                'value' => $v['value'],
//                'id'    => $v['id'],
//                'desc'  => $v['desc'],
//            ];
//        }
        return $config;
    }

    /**
     * 修改
     * @param $id
     * @param $value
     * @return mixed
     * author Yan
     */
    public function update($id, $value)
    {
        return (new Config())->updateOrCreate(['value' => $value], $id);
    }

    /**
     * User: Yan
     * DateTime: 2023/4/11
     * @return array|false|string[] 获取小程序资质图片
     * 获取小程序资质图片
     */
    public function getMiniProve()
    {
        $list = Config::query()->where(['group' => 'mini_prove', 'key' => 'prove'])->value('value');
        if ($list) {
            $list = explode(',', $list);
        } else {
            $list = [];
        }
        return $list;
    }


    /**
     * User: Yan
     * DateTime: 2023/4/11
     * @param $list
     * @return int
     * 更新小程序资质图片
     */
    public function postMiniProve($list): int
    {
        $list = implode(',', $list ?? []) ?? [];
        return Config::query()->where(['group' => 'mini_prove', 'key' => 'prove'])->update(['value' => $list]);
    }
}
