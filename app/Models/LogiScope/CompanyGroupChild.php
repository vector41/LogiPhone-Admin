<?php

namespace App\Models\LogiScope;
use App\Libs\Common\ModelClass;
use App\Scopes\SortIdScope;

class CompanyGroupChild extends ModelClass
{


    protected static function boot(){
        parent::boot();

        static::addGlobalScope(new SortIdScope);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function parent()
    {
        return $this->belongsTo(CompanyGroup::class, "company_group_id", "id");
    }
}
