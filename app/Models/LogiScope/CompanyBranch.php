<?php

namespace App\Models\LogiScope;
use App\Models\Authority\Authority;
use App\Models\Logitimes\CompanySupplierEmployee;
use App\Models\OriginalClass;
use Askedio\SoftCascade\Traits\SoftCascadeTrait;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;


class CompanyBranch extends OriginalClass
{
    use SoftDeletes;
    use SoftCascadeTrait;



    public string $uploadType ="company_branches";
    protected $softCascade = ['children'];
    public array $files = ["document"];

    protected static function boot(){
        parent::boot();
    }

    /**
     * リレーション
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
    public function companyBranch()
    {
        return $this->belongsTo(CompanyBranch::class);
    }

    public function companyEmployees()
    {
        return $this->hasMany(CompanyEmployee::class, "company_branch_id", "id")
            ->selectRaw("*, id as id, CONCAT(person_name_second, ' ', person_name_first) as value");
    }

    public function warehouses()
    {
        return $this->hasMany(CompanyBranchWarehouse::class);
    }

    public function departments()
    {
        return $this->hasMany(new CompanyDepartment);
    }
    public function children()
    {
        return $this->hasMany(new CompanyDepartment);
    }

}
