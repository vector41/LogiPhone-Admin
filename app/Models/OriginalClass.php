<?php
    namespace App\Models;
    use App\Libs\Common\ModelClass;
    use App\Models\Cars\CarEmpty;
    use App\Models\Companies\CompanyBranch;
    use App\Models\Companies\CompanyEmployee;
    use App\Models\Companies\CompanyEmployeeBranch;
    use Illuminate\Database\Eloquent\Builder;
    use Illuminate\Support\Str;

    class OriginalClass extends ModelClass{
        /**
         * 作成者と更新者関連
         * @return mixed
         */
        public function getCreatedUserAttribute()
        {
            return CompanyEmployee::find($this->created_id);
        }
        public function createdUser()
        {
            return $this->belongsTo(CompanyEmployee::class, "created_id", "id");
        }
        public function createdUsers()
        {
            return $this->hasMany(CompanyEmployee::class, "id", "created_id");
        }

        public function getUpdatedUserAttribute()
        {
            return CompanyEmployee::find($this->updated_id);
        }

        /**
         * 会社グループのIDの絞り込み
         */
        public function scopeWhereCompanyGroupId(Builder $builder, int $companyGroupId)
        {
            $builder->where("company_group_id", $companyGroupId);
        }
        /**
         * 会社支店のIDの絞り込み
         */
        public function scopeWhereCompanyEmployeeBranch(Builder $builder, object $employee)
        {
            if (!$employee->is_group_select_type){
                $builder->withWhereHas("companyEmployeeBranch", function($builder2) use($employee){
                    $builder2->where("company_employee_id", $employee->id);
                });
            }
            $builder->withWhereHas("companyBranch.company", function($builder2) use($employee){
                $builder2->where("company_base_id", $employee->company->company_base_id);
            });
        }

        /**
         * 取引先の情報関連
         * @param  Builder  $builder
         * @param  int  $branchId
         * @param  int  $myBranchId
         * @return void
         */
        public function scopeWhereSupplier(Builder $builder, int $branchId, int $myBranchId, $id="company_branch_id", int $mode=1)
        {
            if ($branchId == $myBranchId){
                $builder->where($this->getTable() . "." . $id, -1);
            }else{
                $builder->where($this->getTable() . "." . $id, $branchId);
            }

            $this->whereIsSupplier($myBranchId, $mode);
        }

        public function scopeWhereIsSupplier(Builder $builder, int $myBranchId=0, int $mode=1)
        {
            if ($myBranchId){
                if ($this->getTable() == "company_branches"){
                    $CompanyBranch = CompanyBranch::find($myBranchId);
                    $builder->where($this->getTable() . ".company_id", "<>", $CompanyBranch->company_id);
                    $builder->where("supplier_status", ">=", $mode);
                }else {
                    $this->withWhereHas("companyBranch", function($builder) use($mode){
                        $builder->where("supplier_status", ">=", $mode);
                    });
                }
            }
        }

        /**
         * 入力者関連
         * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
         */
        public function inputUser()
        {
            return $this->belongsTo(CompanyEmployee::class, "input_id")->with("companyBranch");
        }

        public function getInputUserAttribute()
        {
            return CompanyEmployee::find($this->input_id);
        }
        public function companyEmployee()
        {
            return $this->belongsTo(CompanyEmployee::class);
        }

        /**
         *
         * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
         */
        public function companyBranch()
        {
            return $this->belongsTo(CompanyBranch::class, "company_branch_id");
        }

        public function supplierCompanyBranch()
        {
            return $this->belongsTo(CompanyBranch::class, "supplier_company_branch_id");
        }

        public function consignorCompanyBranch()
        {
            return $this->belongsTo(CompanyBranch::class, "consignor_company_branch_id");
        }
        public function charteredCompanyBranch()
        {
            return $this->belongsTo(CompanyBranch::class, "chartered_company_branch_id");
        }
        public function shippingCompanyBranch()
        {
            return $this->belongsTo(CompanyBranch::class, "shipping_company_branch_id");
        }
        public function charteredCompanyEmployee()
        {
            return $this->belongsTo(CompanyEmployee::class,  "chartered_company_employee_id");
        }

        public function consignorCompanyEmployee()
        {
            return $this->belongsTo(CompanyEmployee::class,  "consignor_company_employee_id");
        }
        public function shippingCompanyEmployee()
        {
            return $this->belongsTo(CompanyEmployee::class, "shipping_company_employee_id");
        }
        public function supplierCompanyEmployee()
        {
            return $this->belongsTo(CompanyEmployee::class, "supplier_company_employee_id");
        }


        public function carEmpty()
        {
            return $this->belongsTo(CarEmpty::class);
        }

        public function scopeWhereKeyword(Builder $builder, string $keyword)
        {
            $builder->orWhere("companies.company_name", "LIKE", "%" . $keyword . "%");
            $builder->orWhere("companies.company_name_kana", "LIKE", "%" . $keyword . "%");
            $builder->orWhere("companies.keyword", "LIKE", "%" . $keyword . "%");
            $builder->orWhere("company_branches.branch_name", "%" . $keyword . "%");
            $builder->orWhere("company_branches.branch_name_kana", "%" . $keyword . "%");
            $builder->orWhere("company_branches.keyword", "%" . $keyword . "%");

        }

        public function scopeWhereCompany(Builder $builder, object $request)
        {
            if ($request["company_id"]){
                $builder->where("company_id",$request["company_id"]);
            }

            if ($request["company_branch_id"]){
                $builder->where("company_branch_id",$request["company_branch_id"]);
            }

            if ($request["company_department_id"]){
                $builder->where("company_department_id",$request["company_department_id"]);
            }

            if ($request["company_employee_id"]) {
                $builder->where("id", $request["company_employee_id"]);
            }


        }

        public function relationMaps(string $type)
        {
            $modelName = get_class($this) . "Map";

            return $this->hasMany(new $modelName(),Str::singular($this->getTable()) . "_id", "id")->where("type", $type);
        }

        public function receptions()
        {
            return $this->relationMaps("receptions");
        }
        public function waiting_products()
        {
            return $this->relationMaps("waiting_products");
        }
        public function loadings()
        {
            return $this->relationMaps("loadings");
        }
        public function rests()
        {
            return $this->relationMaps("rests");
        }
        public function waiting_arrivals()
        {
            return $this->relationMaps("waiting_arrivals");
        }
        public function landings()
        {
            return $this->relationMaps("landings");
        }

        public function companyEmployeeBranch()
        {
            return $this->belongsTo(CompanyEmployeeBranch::class, "company_branch_id", "company_branch_id");
        }

    }
