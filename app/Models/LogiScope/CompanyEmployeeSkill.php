<?php

namespace App\Models\LogiScope;
use App\Models\OriginalClass;

class CompanyEmployeeSkill extends OriginalClass
{


    protected static function boot(){
        parent::boot();
    }

    public function skill()
    {
        return $this->belongsTo(CompanySkillChild::class, "company_skill_id", "id");
    }
}
