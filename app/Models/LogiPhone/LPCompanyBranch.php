<?php

namespace App\Models\LogiPhone;
use App\Models\Authority\Authority;
use App\Models\Logitimes\CompanySupplierEmployee;
use App\Models\OriginalClass;
use Askedio\SoftCascade\Traits\SoftCascadeTrait;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;


class LPCompanyBranch extends OriginalClass
{
    use SoftDeletes;
    use SoftCascadeTrait;

    protected $connection = 'mysql_lp';
    protected $table = 'company_branches';
    public string $uploadType ="company_branches";
    public array $files = ["document"];

    protected static function boot(){
        parent::boot();
    }

    /**
     * リレーション
     */
    public function company()
    {
        return $this->belongsTo(LPCompany::class);
    }
    public function companyBranch()
    {
        return $this->belongsTo(LPCompanyBranch::class);
    }

    public function companyEmployees()
    {
        return $this->hasMany(LPCompanyEmployee::class, "company_branch_id", "id")
            ->selectRaw("*, id as id, CONCAT(person_name_second, ' ', person_name_first) as value");
    }

}
