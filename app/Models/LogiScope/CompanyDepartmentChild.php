<?php

    namespace App\Models\LogiScope;
    use App\Models\OriginalClass;

    class CompanyDepartmentChild extends OriginalClass
    {



        protected static function boot(){
            parent::boot();


        }

        public function employees()
        {
            return $this->hasMany(CompanyEmployee::class);
        }
    }
