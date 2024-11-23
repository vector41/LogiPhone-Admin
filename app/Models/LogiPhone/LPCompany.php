<?php

namespace App\Models\LogiPhone;


use App\Libs\Common\ModelClass;
use App\Models\OriginalClass;
use Askedio\SoftCascade\Traits\SoftCascadeTrait;
use Illuminate\Database\Eloquent\SoftDeletes;

class LPCompany extends OriginalClass
{
    use SoftDeletes;
    use SoftCascadeTrait;

    protected $connection = 'mysql_lp';
    protected $table = 'companies';
    protected $softCascade = ['children'];

    protected $guarded = ['id'];
    public string $uploadType = "companies";
    public array $files = ["license", "document"];

    protected $appends = ["company_name_full_short"];

    protected static function boot(){
        parent::boot();
    }

    public function getCompanyNameFullShortAttribute()
    {
        $config = configSearchKey("customs.legal_personality", $this->legal_personality);

        $legal = "(" . mb_substr($config["value"], 0, 1) . ")";

        $result = "";

        if ($this->legal_personality_position == 1){
            $result= $legal . $this->company_name;
        }else{
            $result= $this->company_name . $legal;
        }

        return $result;
    }

    /**
     * 本社の住所などを取得
     * @return void
     */
    public function setMainOfficeInfo()
    {
        $CompanyBranch = LPCompanyBranch::where("is_main_office", 1)
            ->with(["departments.children"])
           ->where("company_id", $this->id)
           ->first();

        $this->zip = $CompanyBranch->zip;
        $this->prefecture = $CompanyBranch->prefecture;
        $this->city = $CompanyBranch->city;
        $this->other = $CompanyBranch->other;
        $this->building = $CompanyBranch->building;
        $this->departments = $CompanyBranch->departments;
    }

    /**
     * リレーション
     */
    public function children()
    {
        return $this->hasMany(LPCompanyBranch::class);
    }

    public function companyMain()
    {
        return $this->hasOne(LPCompanyBranch::class)->where("is_main_office", 1);
    }

}
