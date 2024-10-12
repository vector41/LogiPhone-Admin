<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\Controller;

use App\Libs\Common\ApiClass;
use App\Libs\GroupClass;
use App\Models\LogiPhone\LPCompanyBranch;
use App\Models\LogiPhone\LPCompanyEmployee;
use App\Models\LogiPhone\LPCompanyEmployeeRole;
use App\Models\LogiPhone\LPMergeData;
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
class AppDataController extends Controller
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
            $user = CompanyEmployee::find($user_id);
            $company_in_users = CompanyEmployee::where('company_id',$user->company_id)->get();
            $company_in_user_ids =  $company_in_users->map(function($item){
                return $item->id;
            });

            $store_pos = $request->store_pos??null;
            $main_category = $request->main_category??null;
            $filter_method = $request->filter_method??null;

            $data_builder = LPMergeData::whereIn('updated_id',$company_in_user_ids)->whereNotNull('name');

            if($store_pos)$data_builder->where('store_pos', $store_pos);
            if($main_category)$data_builder->where('main_cat', $main_category);

            $data = $filter_method?$data_builder->whereNotNull("prefecture")->orderBy("prefecture", "asc")->paginate(50):$data_builder->whereNotNull("name")->whereNot("name","="," ")->orderBy("name", "asc")->paginate(50);
            $current_page = $data->currentPage();
            $last_page = $data->lastPage();
            $total = $data->total();
            $per_page = $data->perPage();
            return $ApiClass->responseOk([
                "current_page"=>$current_page,
                "last_page"=>$last_page,
                "total" =>$total,
                "per_page"=>$per_page,
                "response" => $data->items(),
            ]);
        } catch (\Exception $e) {
            //throw $th;
            Log::info($e);
            return $ApiClass->responseError($e->getMessage());
        }
    }

    function getRolesFromEmployeeRole($employeerRoles){
        $roles = [];
        foreach($employeerRoles as $role){
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
    public function get(ApiClass $ApiClass, Request $request )
    {
        $id = $request->id;

        try {
            //code...
            $lp_merge_data = LPMergeData::find($id);

            $detail_data = null;
            if($lp_merge_data){
                if($lp_merge_data->main_cat==1){
                    if($lp_merge_data->store_pos==1){
                        $branch_item = LPCompanyBranch::with('company')->where('id',$lp_merge_data->source_id)->first();
                        $detail_data['name'] =  $branch_item?$branch_item->company->company_name:'';
                        $detail_data['name_kana'] =  $branch_item?$branch_item->company->company_name_kana:'';
                        $detail_data['main_cat'] = 1;
                        $detail_data['store_pos'] = 1;
                        $detail_data['tel'] =  $branch_item?$branch_item->tel:'';
                        $detail_data['fax'] =  $branch_item?$branch_item->fax:'';
                    }else{
                        $branch_item = CompanyBranch::with('company')->where('id',$lp_merge_data->source_id)->first();
                        $detail_data['name'] =  $branch_item?$branch_item->company->company_name:'';
                        $detail_data['name_kana'] =  $branch_item?$branch_item->company->company_name_kana:'';
                        $detail_data['main_cat'] = 1;
                        $detail_data['store_pos'] = 2;
                        $detail_data['tel'] =  $branch_item?$branch_item->tel:'';
                        $detail_data['fax'] =  $branch_item?$branch_item->fax:'';
                    }
                }else{
                    if($lp_merge_data->store_pos==1){
                        $employee_item = LPCompanyEmployee::find($lp_merge_data->source_id);
                        $detail_data['name'] =  $employee_item?$employee_item->person_name_second.' '.$employee_item->person_name_first:'';
                        $detail_data['name_kana'] =  $employee_item?$employee_item->person_name_second_kana.' '.$employee_item->person_name_first_kana:'';
                        $detail_data['main_cat'] = 2;
                        $detail_data['store_pos'] = 1;
                        $detail_data['tel'] =  $employee_item?$employee_item->tel1:'';
                        $detail_data['tel2'] =  $employee_item?$employee_item->tel2:'';
                        $detail_data['tel3'] =  $employee_item?$employee_item->tel3:'';
                    }else{
                        $employee_item = CompanyEmployee::find($lp_merge_data->source_id);
                        $detail_data['name'] =  $employee_item?$employee_item->person_name_second.' '.$employee_item->person_name_first:'';
                        $detail_data['name_kana'] =  $employee_item?$employee_item->person_name_second_kana.' '.$employee_item->person_name_first_kana:'';
                        $detail_data['main_cat'] = 2;
                        $detail_data['store_pos'] = 2;
                        $detail_data['tel'] =  $employee_item?$employee_item->tel1:'';
                        $detail_data['tel2'] =  $employee_item?$employee_item->tel2:'';
                        $detail_data['tel3'] =  $employee_item?$employee_item->tel3:'';
                    }
                }
                return $ApiClass->responseOk([
                    "response" => $detail_data,
                ]);
            }
            return $ApiClass->responseError("保存に失敗しました");
        } catch (\Exception $e) {
            //throw $e;
            return $ApiClass->responseError("保存に失敗しました");
        }
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
                    "company_employee_id"=> $employee_id,
                    "role"=>$role,
                ]);
            }

            DB::commit();

            return $ApiClass->responseOk(['id'=>$employee_id]);
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
            if($type==1){
                $datas = LPCompanyEmployee::where("company_branch_id", $branch_id)->get()
                                    ->append(["employment_roles"]);
                $response = $datas->map(function ($employeer) {
                    return [
                        'id' => $employeer->id,
                        'register'=> CompanyEmployee::find($employeer->created_id)->person_name_second??'',
                        'updater'=>  CompanyEmployee::find($employeer->updated_id)->person_name_second??'',
                        'created_at'=> $employeer->created_at,
                        'updated_at'=> $employeer->updated_at,
                        'person_name' => $employeer->person_name_second.' '.$employeer->person_name_first,
                        'person_name_kana' => $employeer->person_name_second_kana.' '.$employeer->person_name_first_kana,
                        'tel' => $employeer->tel1,
                        'email' => $employeer->email,
                        'position' => $employeer->position,
                        'gender' => $employeer->gender,
                        'roles'=>$employeer->employment_roles,
                        'is_retirement'=>0,
                    ];
                });
                return $ApiClass->responseOk(['response'=>$response]);
            }else{
                $datas = CompanyEmployee::where("company_branch_id", $branch_id)->get()
                                    ->append(["employment_roles"]);
                $response = $datas->map(function ($employeer) {
                    return [
                        'id' => $employeer->id,
                        'register'=> CompanyEmployee::find($employeer->created_id)->person_name_second??'',
                        'updater'=>  CompanyEmployee::find($employeer->updated_id)->person_name_second??'',
                        'created_at'=> $employeer->created_at,
                        'updated_at'=> $employeer->updated_at,
                        'person_name' => $employeer->person_name_second.' '.$employeer->person_name_first,
                        'person_name_kana' => $employeer->person_name_second_kana.' '.$employeer->person_name_first_kana,
                        'tel' => $employeer->tel1,
                        'email' => $employeer->email,
                        'position' => $employeer->position,
                        'gender' => $employeer->gender,
                        'roles'=>$employeer->employment_roles,
                        'is_retirement'=>$employeer->is_retirement
                    ];
                });
                return $ApiClass->responseOk(['response'=>$response]);
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
        try{
            DB::beginTransaction();
            $employeer = $request->employeer;
            $type = $employeer['store_pos'];
            $roles = $employeer["roles"];
            if (!$roles) {
                $roles = [];
            }
            if($type==1){
                $LPCompanyemployeer = LPCompanyEmployee::firstOrNew(["id" => getVariable($employeer, "id")]);
                $LPCompanyemployeer->inputToModel($employeer);
                $LPCompanyemployeer->save();

                $employee_id = $LPCompanyemployeer->id;
                LPCompanyEmployeeRole::where("company_employee_id", $employee_id)->whereNotIn('role',$roles)->delete();
                foreach ($roles as $key => $role) {
                    # code...
                    if(LPCompanyEmployeeRole::where("company_employee_id",$employee_id)->where("role",$role)->exists()==false){
                        LPCompanyEmployeeRole::create([
                            "company_employee_id"=> $employee_id,
                            "role"=>$role,
                        ]);
                    }
                }
            }else{
                $Companyemployeer = CompanyEmployee::firstOrNew(["id" => getVariable($employeer, "id")]);
                $Companyemployeer->inputToModel($employeer);
                $Companyemployeer->save();
                $employee_id = $Companyemployeer->id;
                CompanyEmployeeRole::where("company_employee_id", $employee_id)->whereNotIn('role',$roles)->delete();
                foreach ($roles as $key => $role) {
                    # code...
                    if(CompanyEmployeeRole::where("company_employee_id",$employee_id)->where("role",$role)->exists()==false){
                        CompanyEmployeeRole::create([
                            "company_employee_id"=> $employee_id,
                            "role"=>$role,
                        ]);
                    }
                }
            }

            DB::commit();
            return $ApiClass->responseOk(["id" => $employeer["id"]]);
        }catch (\Exception $e){
            Log::info($e);
            return $ApiClass->responseError($e->getMessage());
        }
    }

    public function getDetailOfEmployee(ApiClass $ApiClass, Request $request){

    }
}
