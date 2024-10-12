<?php

namespace App\Models\LogiPhone;
use App\Models\Authority\Authority;
use App\Models\Logitimes\CompanySupplierEmployee;
use App\Models\OriginalClass;
use Askedio\SoftCascade\Traits\SoftCascadeTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;


class LPMergeData extends Model
{
    protected $connection = 'mysql_lp';
    protected $table = 'merge_datas';
    protected $primaryKey = 'id';

    protected $fillable = [
        'updated_id',
        'store_pos',
        'main_cat',
        'source_id',
        'name',
        'prefecture',
        'tel',
        'tel2',
        'tel3',
    ];
    protected $hidden = [
        'created_at',
        'updated_at',
    ];
}
/* The `all` in the route definitions is specifying the endpoint path for
retrieving all records of a specific resource. For example, `/company/all`
would typically return a list of all companies, `/employees/all` would return a
list of all employees, and so on. It's a common convention in RESTful API
design to use `/resource_name/all` to indicate fetching all records of that
particular resource. */
