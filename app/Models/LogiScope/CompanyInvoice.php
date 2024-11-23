<?php

namespace App\Models\LogiScope;
use App\Models\OriginalClass;

class CompanyInvoice extends OriginalClass
{


    protected static function boot(){
        parent::boot();

    }

    public function children()
    {
        return $this->hasMany(CompanyInvoiceChild::class);
    }

}
