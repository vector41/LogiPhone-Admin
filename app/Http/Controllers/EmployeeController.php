<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

use App\Libs\Common\ApiClass;
use App\Libs\GroupClass;
use App\Models\LogiPhone\LPCompany;
use App\Models\LogiPhone\LPCompanyBranch;
use App\Models\LogiPhone\LPCompanyEmployee;
use App\Models\LogiPhone\LPCompanyEmployeeRole;
use App\Models\LogiPhone\LPEmployee;
use App\Models\LogiPhone\Setting;
use App\Models\LogiScope\Company;
use App\Models\LogiScope\CompanyBranch;
use App\Models\LogiScope\CompanyEmployee;
use App\Models\LogiScope\CompanyEmployeeJobChange;
use App\Models\LogiScope\CompanyEmployeeRole;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


/**
 *	取引先の担当者関連
 */
class EmployeeController extends Controller
{
    /**
     * 一覧の取得
     */
    public function list(ApiClass $ApiClass, Request $request)
    {
        try {
            //code...
            $user = Auth::user();
            $user_id = $user->id;
            $unit_count_by_setting = Setting::where('auth_id', $user_id)->first() ? Setting::where('auth_id', $user_id)->first()->unit_count : 1;
            $unit_count_obj = array_values(array_filter(config('customs.unit_count'), function ($item) use ($unit_count_by_setting) {
                return $item['id'] === $unit_count_by_setting;
            }));
            $unit_count = count($unit_count_obj) > 0 ? $unit_count_obj[0]['value'] : config('customs.unit_count')[0]['value'];
            $company_in_users = CompanyEmployee::where('company_id', $user->company_id)->get();
            $company_in_user_ids = $company_in_users->map(function ($item) {
                return $item->id;
            });

            $store_pos = $request->store_pos;
            $type_cates = $request->type_cates;
            $keyword = $request->keyword;
            $pref = $request->pref;
            $address = $request->address;

            $data_builder = LPEmployee::whereIn('updated_id', $company_in_user_ids);
            if ($store_pos) $data_builder->where('store_pos', $store_pos);
            if ($pref) $data_builder->where('prefecture', $pref);
            if ($address) $data_builder->where('other', 'like', '%' . $address . '%');
            if ($keyword) {
                $data_builder->where(function ($q) use ($keyword) {
                    $q->orWhere('tel1', 'like', '%' . $keyword . '%')
                        ->orWhere('tel2', 'like', '%' . $keyword . '%')
                        ->orWhere('tel3', 'like', '%' . $keyword . '%')
                        ->orWhere('person_name_second', 'like', '%' . $keyword . '%')
                        ->orWhere('person_name_first', 'like', '%' . $keyword . '%')
                        ->orWhere('person_name_first', 'like', '%' . $keyword . '%')
                        ->orWhere('person_name_second_kana', 'like', '%' . $keyword . '%')
                        ->orWhere('person_name_first_kana', 'like', '%' . $keyword . '%')
                        ->orWhere('email', 'like', '%' . $keyword . '%')
                        ->orWhere('company_name', 'like', '%' . $keyword . '%');
                });
            }
            $data = $data_builder->orderBy("id", "asc")->paginate($unit_count_by_setting);

            $response_data = [];
            foreach ($data->items() as $key => $employeer) {
                # code...
                $company = $employeer->store_pos == 2 ? Company::find($employeer->company_id) : LPCompany::find($employeer->company_id);
                $config = configSearchKey("customs.legal_personality", $company->legal_personality);

                $legal = "(" . mb_substr($config["value"], 0, 1) . ")";

                $company_name_full_short = "";

                if ($company->legal_personality_position == 1) {
                    $company_name_full_short = $legal . $company->company_name;
                } else {
                    $company_name_full_short = $company->company_name . $legal;
                }
                $register = $employeer->updated_id ? CompanyEmployee::find($employeer->updated_id) : null;
                $branch = $employeer->store_pos == 2 ? CompanyBranch::find($employeer->company_branch_id) : LPCompanyBranch::find($employeer->company_branch_id);
                $response_data[] = [
                    'id' => $employeer->id,
                    'store_pos' => $employeer->store_pos,
                    'register' => $register ? $register->person_name_second . ' ' . $register->person_name_first : '',
                    'created_at' => $employeer->created_at,
                    'updated_at' => $employeer->updated_at,
                    'person_name' => $employeer->person_name_second . ' ' . $employeer->person_name_first,
                    'person_name_kana' => $employeer->person_name_second_kana . ' ' . $employeer->person_name_first_kana,
                    'tel' => $employeer->tel1,
                    'email' => $employeer->email,
                    'position' => $employeer->position,
                    'gender' => $employeer->gender,
                    'company_name_full_short' => $company_name_full_short,
                    'branch_name' => $branch ? $branch->branch_name : '',
                    'branch_prefecture' => $branch ? $branch->prefecture : '',
                    'branch_city' => $branch ? $branch->city : '',
                    'branch_tel' => $branch ? $branch->tel : '',
                    'roles' => $employeer->employment_roles
                ];
            }
            $current_page = $data->currentPage();
            $last_page = $data->lastPage();
            $total = $data->total();
            return $ApiClass->responseOk([
                "current_page" => $current_page,
                "last_page" => $last_page,
                "total" => $total,
                "response" => $response_data,
            ]);
        } catch (\Exception $e) {
            //throw $th;
            Log::info($e);
            return $ApiClass->responseError($e->getMessage());
        }
    }

    function getRolesFromEmployeeRole($employeerRoles)
    {
        $roles = [];
        foreach ($employeerRoles as $role) {
            array_push($roles, $role->role);
        }
        return $roles;
    }

    public function select(ApiClass $ApiClass, Request $request)
    {
        $CompanyEmployees  = CompanyEmployee::selectRaw("id as id, CONCAT(person_name_second, ' ', person_name_first) as value")->where("company_branch_id", $request->company_branch_id)->get();

        return $ApiClass->responseOk([
            "responses" => $CompanyEmployees
        ]);
    }

    /**
     * 情報取得
     * @param  ApiClass  $ApiClass
     * @param  Request  $request
     * @return object
     */
    public function get(ApiClass $ApiClass, Request $request)
    {
        //法人情報
        $store_pos = $request->store_pos;
        $id = $request->_id;

        $CompanyEmployee = $store_pos == 2 ? CompanyEmployee::where("id", $id)->first()
            ->append(["employment_roles"]) : LPCompanyEmployee::where("id", $id)->first()
            ->append(["employment_roles"]);
        //  ->append(["job_changes", "employment_roles", "board_member", "resume", "license", "photo", "name_card", "other_file"]);

        return $ApiClass->responseOk([
            "response" => $CompanyEmployee,
        ]);
    }

    /**
     * 取引先の更新
     * @param  ApiClass  $ApiClass
     * @param  Request  $request
     * @return object|void
     */
    public function save(ApiClass $ApiClass, Request $request)
    {
        try {
            DB::beginTransaction();

            $CompanyEmployee = CompanyEmployee::firstOrNew(["id" => $request->id]);
            $CompanyBranch = CompanyBranch::find($request->company_branch_id);
            $CompanyEmployee->inputToModel($request->input());
            // $CompanyEmployee->uploadFile($request->input());
            $CompanyEmployee->company_id = $CompanyBranch->company_id;
            $CompanyEmployee->board_member = $request->board_member;
            $CompanyEmployee->save();

            //役割
            $roles = $request->input("employment_roles");

            if (!$roles) {
                $roles = [];
            }

            $CompanyEmployee->updateChildArray(CompanyEmployeeRole::class, $roles, "role");
            DB::commit();

            return $ApiClass->responseOk([]);
        } catch (\Exception $e) {
            Log::info($e);
            return $ApiClass->responseError("保存に失敗しました");
        }
    }

    /**
     * 削除
     * @param  ApiClass  $ApiClass
     * @param  Request  $request
     * @return void
     */
    public function destroy(ApiClass $ApiClass, Request $request)
    {
        try {
            DB::beginTransaction();
            LPCompanyEmployee::whereIn("id", $request->ids)->get();
            DB::commit();
            return $ApiClass->responseOk([]);
        } catch (\Exception $e) {
            Log::info($e);
            return $ApiClass->responseError("削除に失敗しました");
        }
    }

    /**
     * ロジフォン取引先の更新
     * @param  ApiClass  $ApiClass
     * @param  Request  $request
     * @return object|void
     */
    public function saveLP(ApiClass $ApiClass, Request $request)
    {
        try {
            DB::beginTransaction();
            $input = $request->employee;
            $CompanyEmployee = LPCompanyEmployee::firstOrNew(["id" => $input["id"]]);
            $CompanyEmployee->inputToModel($input);
            $CompanyEmployee->save();

            //役割
            $roles = $input["roles"];

            if (!$roles) {
                $roles = [];
            }
            $employee_id = $CompanyEmployee->id;
            LPCompanyEmployeeRole::where("company_employee_id", $employee_id)->delete();
            foreach ($roles as $key => $role) {
                # code...
                LPCompanyEmployeeRole::create([
                    "company_employee_id" => $employee_id,
                    "role" => $role,
                ]);
            }

            DB::commit();

            return $ApiClass->responseOk(['id' => $employee_id]);
        } catch (\Exception $e) {
            Log::info($e);
            return $ApiClass->responseError("保存に失敗しました");
        }
    }

    /**
     * ロジフォン取引先の更新
     * @param  ApiClass  $ApiClass
     * @param  Request  $request
     * @return object|void
     */
    public function getEmployeeFromBranch(ApiClass $ApiClass, Request $request)
    {
        try {

            $type = $request->type;
            $branch_id = $request->id;
            $response = [];
            if ($type == 1) {
                $datas = LPCompanyEmployee::where("company_branch_id", $branch_id)->get()
                    ->append(["employment_roles"]);
                $response = $datas->map(function ($employeer) {
                    return [
                        'id' => $employeer->id,
                        'register' => CompanyEmployee::find($employeer->created_id)->person_name_second ?? '',
                        'updater' =>  CompanyEmployee::find($employeer->updated_id)->person_name_second ?? '',
                        'created_at' => $employeer->created_at,
                        'updated_at' => $employeer->updated_at,
                        'person_name' => $employeer->person_name_second . ' ' . $employeer->person_name_first,
                        'person_name_kana' => $employeer->person_name_second_kana . ' ' . $employeer->person_name_first_kana,
                        'tel' => $employeer->tel1,
                        'email' => $employeer->email,
                        'position' => $employeer->position,
                        'gender' => $employeer->gender,
                        'roles' => $employeer->employment_roles,
                        'is_retirement' => 0,
                    ];
                });
                return $ApiClass->responseOk(['response' => $response]);
            } else {
                $datas = CompanyEmployee::where("company_branch_id", $branch_id)->get()
                    ->append(["employment_roles"]);
                $response = $datas->map(function ($employeer) {
                    return [
                        'id' => $employeer->id,
                        'register' => CompanyEmployee::find($employeer->created_id)->person_name_second ?? '',
                        'updater' =>  CompanyEmployee::find($employeer->updated_id)->person_name_second ?? '',
                        'created_at' => $employeer->created_at,
                        'updated_at' => $employeer->updated_at,
                        'person_name' => $employeer->person_name_second . ' ' . $employeer->person_name_first,
                        'person_name_kana' => $employeer->person_name_second_kana . ' ' . $employeer->person_name_first_kana,
                        'tel' => $employeer->tel1,
                        'email' => $employeer->email,
                        'position' => $employeer->position,
                        'gender' => $employeer->gender,
                        'roles' => $employeer->employment_roles,
                        'is_retirement' => $employeer->is_retirement
                    ];
                });
                return $ApiClass->responseOk(['response' => $response]);
            }
        } catch (\Exception $e) {
            Log::info($e);
            return $ApiClass->responseError("保存に失敗しました");
        }
    }

    /**
     * 連絡先
     * @param  ApiClass  $ApiClass
     * @param  Request  $request
     * @return object|void
     */
    public function updateEmployeer(ApiClass $ApiClass, Request $request)
    {
        try {
            DB::beginTransaction();
            $employeer = $request->employeer;
            $type = $employeer['store_pos'];
            $roles = $employeer["roles"];
            if (!$roles) {
                $roles = [];
            }
            if ($type == 1) {
                $LPCompanyemployeer = LPCompanyEmployee::firstOrNew(["id" => getVariable($employeer, "id")]);
                $LPCompanyemployeer->inputToModel($employeer);
                $LPCompanyemployeer->save();

                $employee_id = $LPCompanyemployeer->id;
                LPCompanyEmployeeRole::where("company_employee_id", $employee_id)->whereNotIn('role', $roles)->delete();
                foreach ($roles as $key => $role) {
                    # code...
                    if (LPCompanyEmployeeRole::where("company_employee_id", $employee_id)->where("role", $role)->exists() == false) {
                        LPCompanyEmployeeRole::create([
                            "company_employee_id" => $employee_id,
                            "role" => $role,
                        ]);
                    }
                }
            } else {
                $Companyemployeer = CompanyEmployee::firstOrNew(["id" => getVariable($employeer, "id")]);
                $Companyemployeer->inputToModel($employeer);
                $Companyemployeer->save();
                $employee_id = $Companyemployeer->id;
                CompanyEmployeeRole::where("company_employee_id", $employee_id)->whereNotIn('role', $roles)->delete();
                foreach ($roles as $key => $role) {
                    # code...
                    if (CompanyEmployeeRole::where("company_employee_id", $employee_id)->where("role", $role)->exists() == false) {
                        CompanyEmployeeRole::create([
                            "company_employee_id" => $employee_id,
                            "role" => $role,
                        ]);
                    }
                }
            }

            DB::commit();
            return $ApiClass->responseOk(["id" => $employeer["id"]]);
        } catch (\Exception $e) {
            Log::info($e);
            return $ApiClass->responseError($e->getMessage());
        }
    }

    public function getEmployees(Request $request)
    {
        $id = $request->id;
        $employees = DB::select("SELECT id, CONCAT(person_name_second,' ', person_name_first) AS name FROM company_employees WHERE company_branch_id = $id");
        return $employees;
    }
}