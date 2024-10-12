<?php

namespace App\Models\LogiScope;

use App\Libs\Common\ModelClass;
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

class CompanyEmployee extends OriginalClass
{
    use HasApiTokens, HasFactory, Notifiable;
    use SoftDeletes;

    public string $uploadType = "company_employees";
    public array $files = ["resume", "license", "photo", "name_card", "other_file"];
    protected $appends = ["person_name", "person_name_kana"];
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new SortScope);
        static::addGlobalScope(new SortIdScope);
    }

    /**
     * 役割の処理
     * @param $value
     */
    public function setEmploymentRolesAttribute($value)
    {
        $this->updateChildArray(CompanyEmployeeRole::class, $value, "role");
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

        $CompanyEmployeeRoles = CompanyEmployeeRole::whereCompanyEmployeeId($this->id)->get();

        foreach ($CompanyEmployeeRoles as $CompanyEmployeeRole) {
            $results[] = $CompanyEmployeeRole->role;
        }

        return $results;
    }

    public function getEmploymentRoleNamesAttribute()
    {
        $results = [];

        foreach ($this->employmentRoles as $data) {
            $results[] = arraySearchId(config("customs.employment_role"), $data);
        }

        return $results;
    }

    public function getBoardMemberAttribute()
    {
        $data = [];
        if ($this->is_board_member) {
            $data[] = 1;
        }

        if ($this->is_representative) {
            $data[] = 2;
        }

        return $data;
    }

    public function setBoardMemberAttribute($values)
    {
        $this->is_board_member = 0;
        $this->is_representative = 0;

        if ($values) {
            foreach ($values as $member) {
                if ($member == 1) {
                    $this->is_board_member = 1;
                }
                if ($member == 2) {
                    $this->is_representative = 1;
                }
            }
        }
    }


    public function scopeWhereActive($builder)
    {
        $builder->where(function (Builder $builder2) {
            $builder2->whereNull($this->getTable() . ".retirement_date")
                ->orWhere($this->getTable() . ".retirement_date", "=", "0000-00-00")
            ;
        });
    }

    public function scopeWhereUse($builder)
    {
        $builder->withWhereHas("companyBranch.company.companyUse", function ($builder) {
            $builder->whereUse();
        });
    }

    public function scopeWhereKeywordEmployee(Builder $builder, string $keyword)
    {
        $builder->orWhere(DB::raw("CONCAT(person_name_second, person_name_first)"), "LIKE", "%" . $keyword . "%");
        $builder->orWhere(DB::raw("CONCAT(person_name_second_kana, person_name_first_kana)"), "LIKE", "%" . $keyword . "%");
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * 支店取得
     */
    public function companyBranch()
    {
        return $this->belongsTo(CompanyBranch::class);
    }
    /**
     * 部署取得
     */
    public function companyDepartment()
    {
        return $this->belongsTo(CompanyDepartment::class);
    }

    public function companyDepartmentChild()
    {
        return $this->belongsTo(CompanyDepartmentChild::class);
    }

    /**
     * ルール
     */
    public function roles()
    {
        return $this->hasMany(CompanyEmployeeRole::class);
    }

    /**
     * 権限
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function companyEmployeeBranches()
    {
        return $this->hasMany(CompanyEmployeeBranch::class, "company_employee_id", "id");
    }

    public function scopeLogin($builder, string $email, string $password)
    {
        $result = $builder->whereEmail($email)->whereUse()
            ->first();

        if (!$result) {
            return;
        }

        $judge = Hash::check($password, $result->password);

        if ($judge) {
            //Auth::guard('time')->loginUsingId($result->id);
            return $result;
        }

        return null;
    }

    public function getCompanyEmployeeBranchArrayAttribute()
    {
        $results = [];

        $companyEmployeeBranches = CompanyEmployeeBranch::where("company_employee_id", $this->id)->get();

        foreach ($companyEmployeeBranches as $companyEmployeeBranch) {
            $results[] = $companyEmployeeBranch->company_branch_id;
        }


        return $results;
    }
    public function skills()
    {
        return $this->hasMany(CompanyEmployeeSkill::class, "company_employee_id", "id");
    }

    public function authority()
    {
        return $this->belongsTo(Authority::class, "authority_id", "id");
    }
}
