<?php

namespace App\Models\LogiScope;


use App\Libs\Common\ModelClass;
use Askedio\SoftCascade\Traits\SoftCascadeTrait;
use Illuminate\Database\Eloquent\SoftDeletes;

class CompanySkill extends ModelClass
{


    protected static function boot(){
        parent::boot();
    }

    public function children()
    {
        return $this->hasMany(CompanySkillChild::class);
    }
}
