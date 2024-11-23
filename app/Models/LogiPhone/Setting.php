<?php

namespace App\Models\LogiPhone;


use App\Libs\Common\ModelClass;
use App\Models\OriginalClass;
use Askedio\SoftCascade\Traits\SoftCascadeTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Setting extends Model
{

    protected $connection = 'mysql_lp';
    protected $table = 'settings';

    protected $primaryKey = 'id';

    protected $fillable = [
        'auth_id',
        'unit_count',
    ];
}
