<?php
    namespace App\Libs;
    use App\Models\Companies\Company;
    use App\Models\Companies\CompanyBase;
    use App\Models\Companies\CompanyBranch;
    use App\Models\Companies\CompanyEmployee;
    // use App\Models\Logitimes\LogitimeCompanyBranch;
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Str;

    class LogiScopeClass{

        public function getPrefix()
        {
            $route = Route::getCurrentRoute();
            $prefix = $route->getPrefix();

            if ($prefix == "api/"){
                $prefix = "scope";
            }

            return str_replace("/", "", $prefix);
        }

        public function getCompanyName(int $legalPersonality, int $legalPersonalityPosition, string $companyName)
        {
            $legal = arraySearchId(config("customs.legal_personality"), $legalPersonality);

            if ((getVariable($legal, "value") && (intval(getVariable($legal, "value")) < 5))){
                $legalName = getVariable($legal, "value");

                $result = "";

                if ($legalPersonalityPosition == 2){
                    $result = $companyName . $legalName ;
                }else{
                    $result = $legalName  . $companyName;
                }
            }else{
                $result = $companyName;
            }

            return $result;
        }

        // public function newCompany($CrudClass, $request)
        // {
        //     //法人情報の保存
        //     $Company = $CrudClass->save(Company::class, $request, [
        //         "legal_personality", "legal_personality_position", "company_name", "license"
        //     ]);
        //     $Company->saveUse();

        //     if ($this->getPrefix() == "time"){
        //         $CompanyBase = CompanyBase::make()->newInstance();
        //         $CompanyBase->save();

        //         $Company->company_base_id = $CompanyBase->id;
        //         $Company->save();
        //     }
        //     //支社の追加
        //     $CompanyBranch = CompanyBranch::make()->newInstance();
        //     $CompanyBranch->branch_name = $CompanyBranch->nickname = "本社";
        //     $CompanyBranch->company_id = $Company->id;
        //     $CompanyBranch->zip = $request->zip;
        //     $CompanyBranch->prefecture = $request->prefecture;
        //     $CompanyBranch->city = $request->city;
        //     $CompanyBranch->other = $request->other;
        //     $CompanyBranch->building = $request->building;
        //     $CompanyBranch->is_main_office = 1;
        //     $CompanyBranch->save();

        //     //従業員の保存
        //     $CompanyEmployee = CompanyEmployee::make()->newInstance();

        //     $CompanyEmployee->company_id = $Company->id;
        //     $CompanyEmployee->company_branch_id = $CompanyBranch->id;
        //     $CompanyEmployee->email = $request->email;
        //     $CompanyEmployee->password = $request->password;
        //     $CompanyEmployee->person_name_second = $request->person_name_second;
        //     $CompanyEmployee->person_name_first = $request->person_name_first;

        //     $CompanyEmployee->changePassword();

        //     $CompanyEmployee->save();


        //     if ($request->company_branch_id){
        //         if (
        //             ($this->getPrefix() == "time") ||
        //             ($request->prefix == "time")
        //         ){
        //             //ロジタイムに関係ある登録
        //             $logitimeCompanyBranchId = $CompanyBranch->id;
        //             $companyBranchId = $request->company_branch_id;

        //             if ($request->prefix){
        //                 $logitimeCompanyBranchId = $request->company_branch_id;
        //                 $companyBranchId = $CompanyBranch->id;
        //             }


        //             $LogitimeCompanyBranch = LogitimeCompanyBranch::where("company_branch_id", $companyBranchId)
        //                 ->where("logitime_company_branch_id", $logitimeCompanyBranchId)
        //                 ->firstOrNew();

        //             $LogitimeCompanyBranch->company_branch_id  = $companyBranchId;
        //             $LogitimeCompanyBranch->logitime_company_branch_id  = $logitimeCompanyBranchId;
        //             $LogitimeCompanyBranch->save();

        //         }

        //     }


        // }
    }
