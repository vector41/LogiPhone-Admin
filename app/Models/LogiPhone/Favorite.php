<?php

namespace App\Models\LogiPhone;


use App\Libs\Common\ModelClass;
use App\Models\OriginalClass;
use Askedio\SoftCascade\Traits\SoftCascadeTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Favorite extends Model
{
    // use SoftDeletes;
    use SoftCascadeTrait;

    protected $connection = 'mysql_lp';
    protected $table = 'favorite';

    protected $primaryKey = 'id';

    protected $fillable = [
        'emp_pos',
        'employeer',
        'fav_pos',
        'fovoriter',
        'main_cat',
    ];

}
