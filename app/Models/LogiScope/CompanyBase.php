<?php

namespace App\Models\LogiScope;


use App\Libs\Common\ModelClass;
use Askedio\SoftCascade\Traits\SoftCascadeTrait;
use Illuminate\Database\Eloquent\SoftDeletes;

class CompanyBase extends ModelClass
{
    use SoftDeletes;
    use SoftCascadeTrait;



    protected static function boot(){
        parent::boot();
    }

}
