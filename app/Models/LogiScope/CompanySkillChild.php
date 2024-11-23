<?php

namespace App\Models\LogiScope;


use App\Libs\Common\ModelClass;
use Askedio\SoftCascade\Traits\SoftCascadeTrait;
use Illuminate\Database\Eloquent\SoftDeletes;

class CompanySkillChild extends ModelClass
{


    protected static function boot(){
        parent::boot();
    }

}
