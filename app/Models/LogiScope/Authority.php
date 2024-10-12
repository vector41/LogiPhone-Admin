<?php

namespace App\Models\LogiScope;

use App\Libs\Common\ModelClass;
use Illuminate\Database\Eloquent\Builder;

class Authority extends ModelClass
{

    /**
     *  タイプごとの整形
     */
    public function getChildrenTypeAttribute()
    {
        $children = $this->children;

        $results = [];

        foreach ($children as $child){
            $results[$child->type] = $child->value;
        }

        return $results;
    }
    public function children()
    {
        return $this->hasMany(AuthorityChild::class);
    }
}
