<?php

namespace App\Utils;

use App\Models\Store\SystemCity;

class City
{
    /**
     * 获取城市列表
     * @return array
     * User: AnJiLan
     */
    public static function getCity(): array
    {
        #获取城市列表
        return  SystemCity::query()->with(['cityTwo'])->where(['parent_id' => 0, 'is_show' => 1])->get(['id', 'city_id', 'name', 'lng', 'lat'])->toArray();

    }


}
