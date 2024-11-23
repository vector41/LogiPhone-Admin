<?php

namespace App\Models;

use App\Libs\Common\ModelClass;
use Illuminate\Database\Eloquent\Builder;

class Area extends ModelClass
{
    /**
     * 都道府県取得
     * @param  Builder  $builder
     * @return Builder
     */
    public function scopeGroupPrefectures(Builder $builder)
    {
        return $builder
            ->select("areas.prefecture", "areas.prefecture_name")
            ->groupBy("areas.prefecture", "areas.prefecture_name");
    }

    public function scopeGroupCity(Builder $builder)
    {
        return $builder
            ->select("prefecture", "city", "city_name", "town_name")
            ->groupBy("prefecture", "city", "city_name", "town_name");

    }
}
