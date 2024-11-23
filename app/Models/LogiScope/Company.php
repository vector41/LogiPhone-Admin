<?php

namespace App\Models\LogiScope;


use App\Libs\Common\ModelClass;
use App\Models\OriginalClass;
use Askedio\SoftCascade\Traits\SoftCascadeTrait;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends OriginalClass
{
    use SoftDeletes;
    use SoftCascadeTrait;



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

    public function getInvoicesAttribute()
    {
        return CompanyInvoice::whereCompanyId($this->id)->get();
    }
    public function getPaymentsAttribute()
    {
        return CompanyPayment::whereCompanyId($this->id)->get();
    }


    /**
     * 加盟組織一覧取得・保存
     * @param $value
     * @return void
     */
    public function getOrganizationsAttribute()
    {
        return CompanyOrganization::where("company_id", $this->id)->get();
    }

    public function setOrganizationsAttribute($value)
    {
        $this->updateChildArray(CompanyOrganization::class, $value, ["organization_name"]);
    }


    /**
     * 本社の住所などを取得
     * @return void
     */
    public function setMainOfficeInfo()
    {
        $CompanyBranch = CompanyBranch::where("is_main_office", 1)
            ->with(["departments.children"])
           ->where("company_id", $this->id)
           ->first();

        $this->zip = $CompanyBranch->zip??'';
        $this->prefecture = $CompanyBranch->prefecture??null;
        $this->city = $CompanyBranch->city??null;
        $this->other = $CompanyBranch->other??'';
        $this->building = $CompanyBranch->building??'';
        $this->departments = $CompanyBranch->departments??[];
    }

    public function saveUse()
    {
        $CompanyUse = CompanyUse::whereCompanyId($this->id)->firstOrNew();
        $CompanyUse->saveUse($this->id);
    }

    /**
     * リレーション
     */
    public function children()
    {
        return $this->hasMany(CompanyBranch::class);
    }

    public function companyMain()
    {
        return $this->hasOne(CompanyBranch::class)->where("is_main_office", 1);
    }

    public function organizations()
    {
        return $this->hasMany(CompanyOrganization::class);
    }
    public function base()
    {
        return $this->belongsTo(CompanyBase::class, "company_base_id", "id");
    }

    public function group()
    {
        return $this->hasOne(CompanyGroupChild::class);
    }

    public function skills()
    {
        return $this->hasMany(CompanySkill::class);
    }

    public function invoices()
    {
        return $this->hasMany(CompanyInvoice::class);
    }
    public function companyUse()
    {
        return $this->hasOne(CompanyUse::class);
    }
}
