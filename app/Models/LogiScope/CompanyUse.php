<?php

namespace App\Models\LogiScope;
use App\Libs\LogiScopeClass;
use App\Models\OriginalClass;
use Illuminate\Support\Facades\Route;

class CompanyUse extends OriginalClass
{


    protected static function boot(){
        parent::boot();
    }

    public function scopeWhereUse($builder)
    {
        $LogiScope = new LogiScopeClass();

        $builder->whereType($LogiScope->getPrefix());
    }

    public function saveUse($companyId)
    {
        $LogiScope = new LogiScopeClass();

        $this->company_id = $companyId;
        $this->type = $LogiScope->getPrefix();
        $this->save();
    }
}
