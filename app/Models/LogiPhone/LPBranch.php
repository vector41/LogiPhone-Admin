<?php

namespace App\Models\LogiPhone;
use App\Models\Authority\Authority;
use App\Models\Logitimes\CompanySupplierEmployee;
use App\Models\OriginalClass;
use Askedio\SoftCascade\Traits\SoftCascadeTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;


class LPBranch extends Model
{
    protected $connection = 'mysql_lp';
    protected $table = 'branches';
    protected $primaryKey = 'id';

    protected $fillable = [
        'created_id',
        'updated_id',
        'store_pos',
        'source_id',
        'company_id',
        'is_main_office',
        'branch_name',
        'nick_name',
        'zip',
        'prefecture',
        'city',
        'other',
        'building',
        'tel',
        'fax',
    ];
}
