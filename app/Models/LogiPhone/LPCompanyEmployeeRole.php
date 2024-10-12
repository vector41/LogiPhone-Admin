<?php

namespace App\Models\LogiPhone;
use App\Models\OriginalClass;

class LPCompanyEmployeeRole extends OriginalClass
{
    protected $connection = 'mysql_lp';
    protected $table = 'company_employee_roles';

    protected static function boot(){
        parent::boot();
    }
}
