<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\Controller;
use App\Libs\Common\ApiClass;
use App\Models\LogiScope\Company;
use App\Models\LogiScope\CompanyBranch;
use App\Models\LogiScope\CompanyEmployee;
use Illuminate\Support\Facades\Auth;
use stdClass;

/**
*	会社関連
*/
class CompanyController extends Controller
{

    /**
     * 会社の情報の取得
     * @return void
     */
    public function list(ApiClass $ApiClass)
    {
        // $user = Auth::user();

        // if ($user->company->company_base_id){
        //     $Companies = Company::whereCompanyBaseId($user->company->company_base_id)->get();
        // }else{
        //     $Companies[] = $user->company;
        // }
        $Companies = Company::whereCompanyBaseId(1)->get();

        foreach ($Companies as $key => $Company){
            if (!$Companies[$key]->is_company_branch){
                //営業所がない場合は本社の住所を入れる
                $Companies[$key]->setMainOfficeInfo();
            }

            $Companies[$key]->children = CompanyBranch::
                with(["children.children"])
                ->where("company_id", $Companies[$key]->id)
                ->get();
        }

        return $ApiClass->responseOk([
            "response" => $Companies
                                     ]);
    }

    /**
     * 社員一覧の取得
     */
    public function employeer_list(ApiClass $ApiClass)
    {
        $list = CompanyEmployee::with(["companyBranch.company","companyDepartment.children", "authority","roles"]);
            // ->whereHas('companyBranch.company', function ($query){
            //     $query->where('company_base_id', 1);
            // });

        $datas = $list->orderBy("id", "desc")->limit(300)->get();

        $response = $datas->map(function ($employeer) {
            return [
                'id' => $employeer->id,
                'person_name' => $employeer->person_name,
                'person_name_kana' => $employeer->person_name_kana,
                'person_nickname' => $employeer->nickname,
                'tel' => $employeer->tel1,
                'hire_date' => $employeer->hire_date,
                'email' => $employeer->email,
                'position' => $employeer->position,
                'gender' => $employeer->gender,
                'department' => $employeer->department,
                'is_retirement' => $employeer->is_retirement,
                'company_name_full_short' =>$employeer->companyBranch->company->company_name_full_short,
                'company_branch' =>([
                        'branch_name' => $employeer->companyBranch->branch_name,
                        'id' => $employeer->companyBranch->id,
                    ]),
                'roles'=>$this->getRolesFromEmployeeRole($employeer->roles)
            ];
        });

        return $ApiClass->responseOk([
                                         "response" => $response
                                     ]);
    }

    function getRolesFromEmployeeRole($employeerRoles){
        $roles = [];
        foreach($employeerRoles as $role){
            array_push($roles, $role->role);
        }
        return $roles;
    }

}
