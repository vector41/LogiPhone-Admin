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
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;

class LPCompanyEmployee extends OriginalClass
{
    // use HasApiTokens, HasFactory, Notifiable;
    use SoftDeletes;

    protected $connection = 'mysql_lp';
    protected $table = 'company_employees';

    public string $uploadType = "company_employees";
    public array $files = ["resume", "license", "photo", "name_card", "other_file"];
    protected $appends = ["person_name", "person_name_kana"];
    protected static function boot(){
        parent::boot();

        static::addGlobalScope(new SortIdScope);
    }

    /**
     * 役割の処理
     * @param $value
     */
    public function setEmploymentRolesAttribute($value)
    {
        $this->updateChildArray(LPCompanyEmployeeRole::class, $value, "role");
    }

    public function getPersonNameAttribute()
    {
        return $this->person_name_second . " " . $this->person_name_first;
    }
    public function getPersonNameKanaAttribute()
    {
        return $this->person_name_second_kana . " " . $this->person_name_first_kana;
    }


    public function getEmploymentRolesAttribute()
    {
        $results = [];

        $CompanyEmployeeRoles = LPCompanyEmployeeRole::whereCompanyEmployeeId($this->id)->get();

        foreach ($CompanyEmployeeRoles as $CompanyEmployeeRole){
            $results[] = $CompanyEmployeeRole->role;
        }

        return $results;

    }

    public function getEmploymentRoleNamesAttribute()
    {
        $results = [];

        foreach ($this->employmentRoles as $data){
            $results[] = arraySearchId(config("customs.employment_role"), $data);
        }

        return $results;
    }

    public function getBoardMemberAttribute()
    {
        $data = [];
        if ($this->is_board_member){
            $data[] = 1;
        }

        if ($this->is_representative){
            $data[] = 2;
        }

        return $data;
    }

    public function setBoardMemberAttribute($values)
    {
        $this->is_board_member = 0;
        $this->is_representative = 0;

        if ($values){
            foreach ($values as $member)
            {
                if ($member == 1){
                    $this->is_board_member = 1;
                }
                if ($member == 2){
                    $this->is_representative = 1;
                }
            }
        }

    }


    public function scopeWhereActive($builder)
    {
        $builder->where(function(Builder $builder2){
            $builder2->whereNull($this->getTable() . ".retirement_date")
                ->orWhere($this->getTable() . ".retirement_date", "=", "0000-00-00")
            ;
        });
    }


    public function scopeWhereKeywordEmployee(Builder $builder, string $keyword)
    {
        $builder->orWhere(DB::raw("CONCAT(person_name_second, person_name_first)"), "LIKE", "%" . $keyword . "%");
        $builder->orWhere(DB::raw("CONCAT(person_name_second_kana, person_name_first_kana)"), "LIKE", "%" . $keyword . "%");
    }

    public function company()
    {
        return $this->belongsTo(LPCompany::class);
    }

    /**
     * 支店取得
     */
    public function companyBranch()
    {
        return $this->belongsTo(LPCompanyBranch::class);
    }

    /**
     * ルール
     */
    public function roles()
    {
        return $this->hasMany(LPCompanyEmployeeRole::class);
    }
}
