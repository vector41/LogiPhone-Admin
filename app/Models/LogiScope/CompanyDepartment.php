<?php

    namespace App\Models\LogiScope;

    use App\Models\OriginalClass;
    use Illuminate\Database\Eloquent\SoftDeletes;

    class CompanyDepartment extends OriginalClass
    {
        use SoftDeletes;



        protected static function boot(){
            parent::boot();

     //       static::addGlobalScope(new SortScope);

        }

        /**
         * リレーション(部署)
         */
        public function children()
        {
            return $this->hasMany(new CompanyDepartmentChild);
        }
        public function employees()
        {
            return $this->hasMany(CompanyEmployee::class);
        }
    }
