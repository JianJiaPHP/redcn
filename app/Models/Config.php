<?php

namespace App\Models;


use Illuminate\Support\Facades\Cache;

class Config extends Base
{

    protected $table = 'config';

    protected $guarded = [];

    /**
     * 根据条件获取
     * @param $where
     * @return array
     * author Yan
     */
    public static function getByWhere($where): array
    {
        return self::query()->whereIn('group', $where)
            ->get(['id', 'key', 'value', 'desc', 'group'])
            ->toArray();
    }

    /**
     * 更新或者添加
     * @param array $params 配置值
     * @param int|null $id 配置ID
     * @author Yan
     */
    public function updateOrCreate(array $params, int $id = null)
    {
        if (is_null($id)) {
            $result = self::query()->create($params);
        } else {
            $result = self::query()->where('id', $id)->update($params);
        }

        self::refresh();

        return $result;
    }

    /**
     * 刷新缓存
     * @return array
     */
    public function refresh(): array
    {
        Cache::forget('config');

        return self::getAll();
    }

    /**
     * 获取所有配置信息
     * @return array
     */
    public function getAll(): array
    {
//        return Cache::rememberForever('config', function () {
        $config = self::query()->get(['group', 'key', 'value'])->toArray();
        $data = [];
        foreach ($config as $v) {
            $data[$v['group'] . "." . $v['key']] = $v['value'];
        }
        return $data;
//        });
    }
}
