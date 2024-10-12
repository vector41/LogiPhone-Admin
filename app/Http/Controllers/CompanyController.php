<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Libs\Common\ApiClass;
use App\Models\LogiPhone\Setting;
use App\Models\LogiScope\Company;
use App\Models\LogiScope\CompanyBranch;
use App\Models\LogiScope\CompanyEmployee;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
// use Request;
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
        $user = Auth::user();
        $Companies = null;
        $Companies = Company::whereCompanyBaseId($user->company_id)->get();

        // return $ApiClass->responseOk(["response" => $Companies]);

        foreach ($Companies as $key => $Company) {
            if (!$Companies[$key]->is_company_branch) {
                //営業所がない場合は本社の住所を入れる
                $Companies[$key]->setMainOfficeInfo();
            }

            $Companies[$key]->children = CompanyBranch::with(["children.children"])
                ->where("company_id", $Companies[$key]->id)
                ->get();
        }

        return $ApiClass->responseOk(["response" => $Companies]);
    }

    /**
     * 社員一覧の取得
     */
    public function employeer_list(ApiClass $ApiClass)
    {
        $user = Auth::user();
        $user_id = $user->id;
        $company_id = $user->company_id;
        $unit_count_by_setting = Setting::where('auth_id', $user_id)->first() ? Setting::where('auth_id', $user_id)->first()->unit_count : 1;
        $unit_count_obj = array_values(array_filter(config('customs.unit_count'), function ($item) use ($unit_count_by_setting) {
            return $item['id'] === $unit_count_by_setting;
        }));
        $unit_count = count($unit_count_obj) > 0 ? $unit_count_obj[0]['value'] : config('customs.unit_count')[0]['value'];
        $list = CompanyEmployee::with(["companyBranch.company", "companyDepartment.children", "authority", "roles"]);
        $data = $list->where('company_id', $company_id)->orderBy("id", "desc")->paginate($unit_count);

        $response = $data->map(function ($employeer) {
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
                'company_name_full_short' => $employeer->companyBranch->company->company_name_full_short,
                'company_branch' => ([
                    'branch_name' => $employeer->companyBranch->branch_name,
                    'id' => $employeer->companyBranch->id,
                ]),
                'roles' => $this->getRolesFromEmployeeRole($employeer->roles)
            ];
        });
        $current_page = $data->currentPage();
        $last_page = $data->lastPage();
        $total = $data->total();
        return $ApiClass->responseOk([
            "current_page" => $current_page,
            "last_page" => $last_page,
            "total" => $total,
            "response" => $response
        ]);
    }

    function getRolesFromEmployeeRole($employeerRoles)
    {
        $roles = [];
        foreach ($employeerRoles as $role) {
            array_push($roles, $role->role);
        }
        return $roles;
    }


    public function getBranches(Request $request)
    {
        $companyID = $request->companyID;
        $branches = DB::select("SELECT id,branch_name AS office FROM company_branches WHERE company_id = $companyID");
        return $branches;
    }
}