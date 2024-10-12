<?php

namespace App\Models\LogiScope;
use App\Models\OriginalClass;

class CompanyGroup extends OriginalClass
{


    protected static function boot(){
        parent::boot();
    }

    public function children()
    {
        return $this->hasMany(CompanyGroupChild::class);
    }
}
