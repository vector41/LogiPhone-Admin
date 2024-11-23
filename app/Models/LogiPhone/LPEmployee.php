<?php

namespace App\Models\LogiPhone;
use App\Libs\Common\ModelClass;
use App\Models\Authority\Authority;
use App\Models\Cars\Car;
use App\Models\OriginalClass;
use App\Scopes\SortIdScope;
use App\Scopes\SortScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;

class LPEmployee extends Model
{
    protected $connection = 'mysql_lp';
    protected $table = 'employees';
    protected $primaryKey = 'id';

    protected $fillable = [
        'created_id',
        'updated_id',
        'store_pos',
        'source_id',
        'company_id',
        'company_branch_id',
        'company_department_id',
        'department',
        'company_department_child_id',
        'person_name_second',
        'person_name_first',
        'person_name_second_kana',
        'person_name_first_kana',
        'position',
        'is_representative',
        'is_board_member',
        'is_retirement',
        'nickname',
        'tel1',
        'tel2',
        'tel3',
        'birth_date',
        'gender',
        'zip',
        'prefecture',
        'city',
        'other',
        'building',
        'email',
        'company_name',
    ];
}
