<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\Controller;

use App\Libs\Common\ApiClass;
use App\Libs\GroupClass;
use App\Models\LogiPhone\LPCompanyBranch;
use App\Models\LogiPhone\LPCompanyEmployee;
use App\Models\LogiPhone\LPCompanyEmployeeRole;
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
            //$user = Auth::user();
            ini_set('memory_limit', '2048M');
            $user_id = 1;
            $user_company_id = CompanyEmployee::find($user_id)->company_id;
            $users_from_company = CompanyEmployee::where("company_id", $user_company_id)->select('id')->get();
            $user_ids = $users_from_company->map(function ($user) {
                return $user->id;
            })->toArray();

            $datas = CompanyEmployee::with(["companyBranch"])
                ->where(function ($builder) {
                    $builder->orWhereNull("company_employees.is_retirement");
                    $builder->orWhere("company_employees.is_retirement", "<>", 1);
                })
                ->whereHas('companyBranch', function ($query) use ($user_ids) {
                    $query->orWhereIn("updated_id", $user_ids);
                })->orderBy("id", "desc")->limit(40)->get();

            $response_2 = $datas->map(function ($employeer) {
                return [
                    'id' => $employeer->id,
                    'store_pos' => 2,    // 2: logiscope  1:logiphone...
                    'person_name' => $employeer->person_name_second . ' ' . $employeer->person_name_first,
                    'tel' =>  $employeer->tel1 != "--" ? $employeer->tel1 : $employeer->tel2,
                    'email' => $employeer->email,
                ];
            });

            $lp_company_branches = LPCompanyBranch::where("company_id", $user_company_id)->get();

            $datas = LPCompanyEmployee::with(["company", "companyBranch"])
                ->orderBy("id", "DESC")->limit(40)->get()->append(["employment_roles"]);
            $response_1 = $datas->map(function ($employeer) {
                return [
                    'id' => $employeer->id,
                    'store_pos' => 1,    // 2: logiscope  1:logiphone...
                    'person_name' => $employeer->person_name_second . ' ' . $employeer->person_name_first,
                    'tel' => $employeer->tel1 != "--" ? $employeer->tel1 : $employeer->tel2,
                    'email' => $employeer->email,
                ];
            });
            $response = count($response_1) ? $response_1->merge($response_2) : $response_2;
            // return response()->json($response->toArray());
            return  $ApiClass->responseOk([
                "response" => $response
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

    // android code 
    public function getAllUsersByPage(Request $request)
    {
        $users = CompanyEmployee::paginate(50);
        return response()->json($users);
    }

    public function getSpecificUser(Request $request)
    {
        $searchIndex = $request->route('keyword');
        $users = CompanyEmployee::where('tel1', '=', $searchIndex)
                                ->orWhere('tel2', '=', $searchIndex)
                                ->orWhere('tel3', '=', $searchIndex)
                                ->get();

        return response()->json($users);
    }
}
