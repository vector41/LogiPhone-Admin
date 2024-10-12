<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Libs\Common\ApiClass;
use App\Libs\GroupClass;
use App\Models\LogiPhone\LPBranch;
use App\Models\LogiPhone\LPCompany;
use App\Models\LogiPhone\LPCompanyBranch;
use App\Models\LogiPhone\LPCompanyEmployee;
use App\Models\LogiPhone\LPEmployee;
use App\Models\LogiPhone\LPMergeData;
use App\Models\LogiScope\Company;
use App\Models\LogiScope\CompanyBranch;
use App\Models\LogiScope\CompanyBranchWarehouse;
use App\Models\LogiScope\CompanyEmployee;
use App\Models\Suppliers\SupplierBranch;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use stdClass;
// use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Date;

/**
*
*/
class ProccessController extends Controller
{
    /**
     * 取引先関連の処理
     */
    public function ls_branch_process(ApiClass $ApiClass, Request $request)
    {
        ini_set('max_execution_time', 200); // Set max execution time to 300 seconds (5 minutes)

        $last_ls_id = LPBranch::where('store_pos',2)->max('source_id')??0;
       // $datas = CompanyBranch::where('id','>',$last_ls_id)->orderBy('id')->limit(2000)->get();
        $datas = LPBranch::where('store_pos',2)->where('company_name',null)->orderBy('id')->limit(2000)->get();
        $max_branch_id = 0;
        try {
            //code...
            $startTime = Date::now();
            foreach ($datas as $key => $company_branch) {
                # code...
                $endTime = Date::now();
                if ($endTime->diffInSeconds($startTime) > 50) {
                    return response()->json(['status' => 'continue', 'max_id' =>  $max_branch_id]);
                }

                // $branch = LPBranch::create([
                //     'created_id'=>$company_branch->created_id,
                //     'updated_id'=>$company_branch->updated_id,
                //     'store_pos'=>2,
                //     'source_id'=>$company_branch->id,
                //     'company_id'=>$company_branch->company_id,
                //     'is_main_office'=>$company_branch->is_main_office,
                //     'branch_name'=>$company_branch->branch_name,
                //     'nick_name'=>$company_branch->nick_name,
                //     'zip'=>$company_branch->zip,
                //     'prefecture'=>$company_branch->prefecture,
                //     'city'=>$company_branch->city,
                //     'other'=>$company_branch->other,
                //     'building'=>$company_branch->building,
                //     'tel'=>$company_branch->tel,
                //     'fax'=>$company_branch->fax,
                // ]);
                // $branch->created_at=$company_branch->created_at;
                // $branch->updated_at=$company_branch->updated_at;
                // $branch->deleted_at=$company_branch->deleted_at;
                // $branch->save();

                // $max_branch_id=$branch->id;

                $source_company = Company::find($company_branch->company_id);
                $source_branch = CompanyBranch::find($company_branch->source_id);
                $company_branch->company_name=$source_company->company_name;
                $company_branch->created_at=$source_branch->created_at;
                $company_branch->updated_at=$source_branch->updated_at;
                $company_branch->deleted_at=$source_branch->deleted_at;
                $company_branch->save();
                $max_branch_id=$company_branch->id;
            }

            return  response()->json([
                'status' => 'ok',
                'datas' =>  $max_branch_id
            ]);
        } catch (\Throwable $th) {
            //throw $th;
            Log::info($th);
            return response()->json(['status' => 'error', 'message' =>  $th->getMessage()]);
        }
    }

        /**
     * 取引先関連の処理
     */
    public function lp_branch_process(ApiClass $ApiClass, Request $request)
    {
        ini_set('max_execution_time', 200); // Set max execution time to 300 seconds (5 minutes)

        $last_ls_id = LPBranch::where('store_pos',1)->max('source_id')??0;
        $datas = LPCompanyBranch::where('id','>',$last_ls_id)->orderBy('id')->limit(2000)->get();
        $max_branch_id = 0;
        try {
            //code...
            $startTime = Date::now();
            foreach ($datas as $key => $company_branch) {
                # code...
                $endTime = Date::now();
                if ($endTime->diffInSeconds($startTime) > 50) {
                    return response()->json(['status' => 'continue', 'max_id' =>  $max_branch_id]);
                }
                $branch = LPBranch::create([
                    'created_id'=>$company_branch->created_id,
                    'updated_id'=>$company_branch->updated_id,
                    'store_pos'=>1,
                    'source_id'=>$company_branch->id,
                    'company_id'=>$company_branch->company_id,
                    'is_main_office'=>$company_branch->is_main_office,
                    'branch_name'=>$company_branch->branch_name,
                    'nick_name'=>$company_branch->nick_name,
                    'zip'=>$company_branch->zip,
                    'prefecture'=>$company_branch->prefecture,
                    'city'=>$company_branch->city,
                    'other'=>$company_branch->other,
                    'building'=>$company_branch->building,
                    'tel'=>$company_branch->tel,
                    'fax'=>$company_branch->fax,
                ]);

                $branch->created_at=$company_branch->created_at;
                $branch->updated_at=$company_branch->updated_at;
                $branch->deleted_at=$company_branch->deleted_at;
                $branch->save();

                $max_branch_id=$branch->id;
            }

            return  response()->json([
                'status' => 'ok',
                'datas' =>  $max_branch_id
            ]);
        } catch (\Throwable $th) {
            //throw $th;
            Log::info($th);
            return response()->json(['status' => 'error', 'message' =>  $th->getMessage()]);
        }
    }

    /**
     * 取引先関連の処理
     */
    public function ls_employee_process(ApiClass $ApiClass, Request $request)
    {
        ini_set('max_execution_time', 200); // Set max execution time to 300 seconds (5 minutes)

        $last_ls_id = LPEmployee::where('store_pos',2)->max('source_id')??0;
       // $datas = CompanyEmployee::where('id','>',$last_ls_id)->orderBy('id')->limit(2000)->get();
        $datas = LPEmployee::where('store_pos',2)->where('company_name',null)->orderBy('id')->limit(2000)->get();
        $max_employee_id = 0;
        try {
            //code...
            $startTime = Date::now();
            foreach ($datas as $key => $company_employee) {
                # code...
                $endTime = Date::now();
                if ($endTime->diffInSeconds($startTime) > 50) {
                    return response()->json(['status' => 'continue', 'max_id' =>  $max_employee_id]);
                }
                // $employee = LPEmployee::create([
                //     'created_id'=>$company_employee->created_id,
                //     'updated_id'=>$company_employee->updated_id,
                //     'store_pos'=>2,
                //     'source_id'=>$company_employee->id,
                //     'company_id'=>$company_employee->company_id,
                //     'company_branch_id'=>$company_employee->company_branch_id,
                //     'company_department_id'=>$company_employee->company_department_id,
                //     'department'=>$company_employee->department,
                //     'company_department_child_id'=>$company_employee->company_department_child_id,
                //     'person_name_second'=>$company_employee->person_name_second,
                //     'person_name_first'=>$company_employee->person_name_first,
                //     'person_name_second_kana'=>$company_employee->person_name_second_kana,
                //     'person_name_first_kana'=>$company_employee->person_name_first_kana,
                //     'position'=>$company_employee->position,
                //     'is_representative'=>$company_employee->is_representative,
                //     'is_board_member'=>$company_employee->is_board_member,
                //     'is_retirement'=>$company_employee->is_retirement,
                //     'nickname'=>$company_employee->nickname,
                //     'tel1'=>$company_employee->tel1,
                //     'tel2'=>$company_employee->tel2,
                //     'tel3'=>$company_employee->tel3,
                //     'birth_date'=>$company_employee->birth_date,
                //     'gender'=>$company_employee->gender,
                //     'zip'=>$company_employee->zip,
                //     'prefecture'=>$company_employee->prefecture,
                //     'city'=>$company_employee->city,
                //     'other'=>$company_employee->other,
                //     'building'=>$company_employee->building,
                //     'email'=>$company_employee->email,
                // ]);

                $source_employee = CompanyEmployee::find($company_employee->source_id);
                $source_company  = Company::find($company_employee->company_id);
                $company_employee->company_name=$source_company->company_name;
                $company_employee->created_at=$source_employee->created_at;
                $company_employee->updated_at=$source_employee->updated_at;
                $company_employee->deleted_at=$source_employee->deleted_at;
                $company_employee->save();
                $max_employee_id=$company_employee->id;

                // $employee->created_at=$company_employee->created_at;
                // $employee->updated_at=$company_employee->updated_at;
                // $employee->deleted_at=$company_employee->deleted_at;
                // $employee->save();

                // $max_employee_id=$employee->id;
            }

            return  response()->json([
                'status' => 'ok',
                'datas' =>  $max_employee_id
            ]);
        } catch (\Throwable $th) {
            //throw $th;
            Log::info($th);
            return response()->json(['status' => 'error', 'message' =>  $th->getMessage()]);
        }
    }

        /**
     * 取引先関連の処理
     */
    public function lp_employee_process(ApiClass $ApiClass, Request $request)
    {
        ini_set('max_execution_time', 200); // Set max execution time to 300 seconds (5 minutes)

        $last_ls_id = LPEmployee::where('store_pos',1)->max('source_id')??0;
        // $datas = LPCompanyEmployee::where('id','>',$last_ls_id)->orderBy('id')->limit(2000)->get();
        $datas = LPEmployee::where('store_pos',1)->where('company_name',null)->orderBy('id')->limit(2000)->get();
        $max_employee_id = 0;
        try {
            //code...
            $startTime = Date::now();
            foreach ($datas as $key => $company_employee) {
                # code...
                $endTime = Date::now();
                if ($endTime->diffInSeconds($startTime) > 50) {
                    return response()->json(['status' => 'continue', 'max_id' =>  $max_employee_id]);
                }
                // $employee = LPEmployee::create([
                //     'created_id'=>$company_employee->created_id,
                //     'updated_id'=>$company_employee->updated_id,
                //     'store_pos'=>1,
                //     'source_id'=>$company_employee->id,
                //     'company_id'=>$company_employee->company_id,
                //     'company_branch_id'=>$company_employee->company_branch_id,
                //     'company_department_id'=>$company_employee->company_department_id,
                //     'department'=>$company_employee->department,
                //     'company_department_child_id'=>$company_employee->company_department_child_id,
                //     'person_name_second'=>$company_employee->person_name_second,
                //     'person_name_first'=>$company_employee->person_name_first,
                //     'person_name_second_kana'=>$company_employee->person_name_second_kana,
                //     'person_name_first_kana'=>$company_employee->person_name_first_kana,
                //     'position'=>$company_employee->position,
                //     'is_representative'=>$company_employee->is_representative,
                //     'is_board_member'=>$company_employee->is_board_member,
                //     'is_retirement'=>$company_employee->is_retirement,
                //     'nickname'=>$company_employee->nickname,
                //     'tel1'=>$company_employee->tel1,
                //     'tel2'=>$company_employee->tel2,
                //     'tel3'=>$company_employee->tel3,
                //     'birth_date'=>$company_employee->birth_date,
                //     'gender'=>$company_employee->gender,
                //     'zip'=>$company_employee->zip,
                //     'prefecture'=>$company_employee->prefecture,
                //     'city'=>$company_employee->city,
                //     'other'=>$company_employee->other,
                //     'building'=>$company_employee->building,
                //     'email'=>$company_employee->email,
                // ]);

                // $employee->created_at=$company_employee->created_at;
                // $employee->updated_at=$company_employee->updated_at;
                // $employee->deleted_at=$company_employee->deleted_at;
                // $employee->save();

                // $max_employee_id=$employee->id;

                $source_employee = CompanyEmployee::find($company_employee->source_id);
                $source_company  = LPCompany::find($company_employee->company_id);
                $company_employee->company_name=$source_company->company_name;
                $company_employee->created_at=$source_employee->created_at;
                $company_employee->updated_at=$source_employee->updated_at;
                $company_employee->deleted_at=$source_employee->deleted_at;
                $company_employee->save();
                $max_employee_id=$company_employee->id;
            }

            return  response()->json([
                'status' => 'ok',
                'datas' =>  $max_employee_id
            ]);
        } catch (\Throwable $th) {
            //throw $th;
            Log::info($th);
            return response()->json(['status' => 'error', 'message' =>  $th->getMessage()]);
        }
    }

    public function merge_app_process(ApiClass $ApiClass, Request $request){
        ini_set('max_execution_time', 200); // Set max execution time to 300 seconds (5 minutes)

        // $last_branch_id = LPMergeData::where('store_pos',1)->where('main_cat',2)->max('source_id')??0;
        // $datas = LPEmployee::where('store_pos',1)->where('source_id','>',$last_branch_id)->orderBy('id')->limit(2000)->get();
        $datas = LPMergeData::where('main_cat',1)->where(function($q){
                            $q->orWhere('name', 'like', '%(株)%')
                            ->orWhere('name', 'like', '%(有)%')
                            ->orWhere('name', 'like', '%(協)%')
                            ->orWhere('name', 'like', '%(合)%')
                            ->orWhere('name', 'like', '%(上)%');
                        })->orderBy('id')->limit(5000)->get();
        $max_merge_id = 0;
        try {
            //code...
            // $startTime = Date::now();
            foreach ($datas as $key => $branch) {
                # code...
                // $endTime = Date::now();
                // if ($endTime->diffInSeconds($startTime) > 50) {
                //     return response()->json(['status' => 'continue', 'max_id' =>  $max_merge_id]);
                // }

                // $company = $employee->store_pos==2?Company::find($employee->company_id):LPCompany::find($employee->company_id);
                // $config = configSearchKey("customs.legal_personality", $company->legal_personality);

                // $employee_name = $employee->person_name_second.' '.$employee->person_name_first;
                // $merge_item = LPMergeData::create([
                //     'updated_id'=>$employee->updated_id,
                //     'store_pos'=>1,
                //     'main_cat'=>2,
                //     'source_id'=>$employee->source_id,
                //     'name'=>$employee_name,
                //     'prefecture'=>$employee->prefecture,
                //     'tel'=>$employee->tel1,
                //     'tel2'=>$employee->tel2,
                //     'tel3'=>$employee->tel3,
                // ]);

                // $max_merge_id=$merge_item->id;

                // $merge_item = LPMergeData::where('store_pos',2)->where('main_cat',1)->where('source_id', $branch->source_id)->first();
                // if($merge_item){
                //     $merge_item->prefecture = 1;
                //     $merge_item->save();
                //     $max_merge_id=$branch->id;
                // }
                $branch->name = ltrim(str_replace(["(株)", "(有)", "(協)", "(合)", "(上)"], "", $branch->name));
                $branch->save();
            }

            return  response()->json([
                'status' => 'ok',
            ]);
        } catch (\Throwable $th) {
            //throw $th;
            Log::info($th);
            return response()->json(['status' => 'error', 'message' =>  $th->getMessage()]);
        }
    }

}
