<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Libs\Common\ApiClass;
use App\Libs\GroupClass;
use App\Models\LogiPhone\LPBranch;
use App\Models\LogiPhone\LPCompany;
use App\Models\LogiPhone\LPCompanyBranch;
use App\Models\LogiPhone\Setting;
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
use Symfony\Component\HttpFoundation\File\File;

/**
*	取引先関連
*/
class SupplierController extends Controller
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
            $unit_count_by_setting = Setting::where('auth_id',$user_id)->first()?Setting::where('auth_id',$user_id)->first()->unit_count:50;
            $unit_count_obj = array_values(array_filter(config('customs.unit_count'), function($item) use ($unit_count_by_setting){
                return $item['id'] === $unit_count_by_setting;
            }));
            // return $unit_count_obj;
            $unit_count = count($unit_count_obj) > 0 ? $unit_count_obj[0]['value']:config('customs.unit_count')[0]['value'];
            // return $unit_count;
            // $user = CompanyEmployee::find($user_id);
            $company_in_users = CompanyEmployee::where('company_id',$user->company_id)->get();
            $company_in_user_ids =  $company_in_users->map(function($item){
                return $item->id;
            });

            $store_pos = $request->store_pos;
            $type_cates = $request->type_cates;
            $keyword = $request->keyword;
            $pref = $request->pref;
            $address = $request->address;

            $data_builder = LPBranch::whereIn('updated_id',$company_in_user_ids);

            if($store_pos)$data_builder->where('store_pos', $store_pos);
            if($pref)$data_builder->where('prefecture', $pref);
            if($address)$data_builder->where('other', 'like', '%'.$address.'%');
            if($keyword){
                $data_builder->where(function($q) use ($keyword){
                    $q->orWhere('branch_name', 'like', '%'.$keyword.'%')
                      ->orWhere('tel', 'like', '%'.$keyword.'%')
                      ->orWhere('company_name', 'like', '%'.$keyword.'%');
                });
            }
            $data = $data_builder->orderBy("id", "asc")->paginate($unit_count_by_setting);
            $response_data = [];
            foreach ($data->items() as $key => $branch) {
                # code...
                $company = $branch->store_pos == 2?Company::find($branch->company_id):LPCompany::find($branch->company_id);
                $config = configSearchKey("customs.legal_personality", $company->legal_personality);

                $legal = "(" . mb_substr($config["value"], 0, 1) . ")";

                $company_name_full_short = "";

                if ($company->legal_personality_position == 1){
                    $company_name_full_short= $legal . $company->company_name;
                }else{
                    $company_name_full_short= $company->company_name . $legal;
                }
                $register = $branch->updated_id?CompanyEmployee::find($branch->updated_id):null;
                $response_data[]=[
                                'id' => $branch->source_id,
                                'store_pos'=>$branch->store_pos,
                                'register'=>$register?$register->person_name_second.' '.$register->person_name_first:'',
                                'branch_name' => $branch->branch_name,
                                'prefecture'=> $branch->prefecture,
                                'tel' => $branch->tel,
                                'fax' => $branch->fax,
                                'keyword' => $branch->keyword,
                                'other' => $branch->other,
                                'building' => $branch->building,
                                'company_name_full_short' => $company_name_full_short,
                                // 'company_name' => $company->company_name,
                            ];
            }
            $current_page = $data->currentPage();
            $last_page = $data->lastPage();
            $total = $data->total();
            return $ApiClass->responseOk([
                "current_page"=>$current_page,
                "last_page"=>$last_page,
                "total" =>$total,
                "response" => $response_data,
            ]);
        } catch (\Exception $e) {
            //throw $th;
            Log::info($e);
            return $ApiClass->responseError($e->getMessage());
        }
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
        $Company = Company::with(["organizations", "permits", "children.warehouses", "group.parent"])->find($request->id);

        if ($Company){
            $response = $Company;
            // $response->children->append(["car_request_sites", "document"]);
        }else{
            $response = new stdClass();
        }

        return $ApiClass->responseOk([
            "response" => $response,
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
        try{
            DB::beginTransaction();

            $input = $request->input();
            $Company = Company::firstOrNew(["id" => getVariable($input, "id")]);
            $Company->inputToModel($input);

            if (empty($input["id"])){
                //ここで作成された物はステータスを2にする
                $Company->supplier_status = 2;
            }

            $Company->save();
            // $Company->uploadFile($input);

            $Company->organizations = getVariable($input, "organizations", []);
            $Company->permits = getVariable($input, "permits", []);

            if (!$input["is_company_branch"]){
                $input["children"][0]["branch_name"] = "本社";
                $input["children"][0]["is_main_office"] = 1;
            }

            if (!empty($input["children"])){
                foreach ($input["children"] as $branch){
                    //存在した場合は支店情報の保存
                    $CompanyBranch = CompanyBranch::firstOrNew(["id" => getVariable($branch, "id")]);
                    $CompanyBranch->uploadFile($branch);
                    $CompanyBranch->company_id = $Company->id;

                    $CompanyBranch->inputToModel($branch);
                    $CompanyBranch->supplier_status = $Company->supplier_status;

                    $CompanyBranch->save();

                    $CompanyBranch->car_request_sites = getVariable($branch, "car_request_sites", []);

                    $GroupClass = new GroupClass();
                    $GroupClass->update(CompanyBranchWarehouse::class, 0, getVariable($branch, "warehouses"), ["company_branch_id", $CompanyBranch->id]);
                }
            }

            DB::commit();

            return $ApiClass->responseOk(["id" => $Company->id, "company_branch_id" => $CompanyBranch->id]);
        }catch (\Exception $e){
            Log::info($e);
            return $ApiClass->responseError("保存に失敗しました");
        }
    }

    /**
     * 削除 LOGIPHONE ONLY...
     * @param  ApiClass  $ApiClass
     * @param  Request  $request
     * @return void
     */
    public function destroy(ApiClass $ApiClass, Request $request)
    {
        try{
            DB::beginTransaction();
            LPCompanyBranch::whereIn("id", $request->ids)->delete();
            DB::commit();

            return $ApiClass->responseOk([]);
        }catch (\Exception $e){
            Log::info($e);
            return $ApiClass->responseError("削除に失敗しました");
        }
    }

        /**
     * 取引先の更新
     * @param  ApiClass  $ApiClass
     * @param  Request  $request
     * @return object|void
     */
    public function saveLP(ApiClass $ApiClass, Request $request)
    {
        try{
            DB::beginTransaction();
            $input = $request->company;

            $LPCompany = LPCompany::firstOrNew(["id" => getVariable($input, "id")]);
            $LPCompany->inputToModel($input);

            $LPCompany->save();
           // $LPCompany->uploadFile($input);

           LPCompanyBranch::where('company_id',$LPCompany->id)->delete();
            if (!$input["is_company_branch"]){
                $input["children"][0]["branch_name"] = "本社";
                $input["children"][0]["is_main_office"] = 1;
            }
            if (!empty($input["children"])){
                foreach ($input["children"] as $branch){
                    //存在した場合は支店情報の保存
                    $LPCompanyBranch = LPCompanyBranch::firstOrNew(["id" => getVariable($branch, "id")]);
                  //  $LPCompanyBranch->uploadFile($branch);
                    $LPCompanyBranch->company_id = $LPCompany->id;
                    $LPCompanyBranch->inputToModel($branch);
                    $LPCompanyBranch->save();
                }
            }

            DB::commit();
            return $ApiClass->responseOk(["id" => $LPCompany->id]);

        }catch (\Exception $e){
            Log::info($e);
            return $ApiClass->responseError("保存に失敗しました");
        }
    }

    /**
     * ロジフォン連絡先
     * @param  ApiClass  $ApiClass
     * @param  Request  $request
     * @return object|void
     */
    public function getBranchLPList(ApiClass $ApiClass, Request $request)
    {
        try{
            $datas = LPCompanyBranch::with(["company"])->orderBy("company_branches.id", "DESC")->limit(300)->get();
            $response = $datas->map(function ($branch) {
                return [
                    'id' => $branch->id,
                    'type'=>1,
                    'branch_name' => $branch->branch_name,
                    'prefecture'=> $branch->prefecture,
                    'tel' => $branch->tel,
                    'fax' => $branch->fax,
                    'keyword' => $branch->keyword,
                    'other' => $branch->other,
                    'building' => $branch->building,
                    'company_name_full_short' => $branch->company->company_name_full_short,
                    'company_name' => $branch->company->company_name,
                ];
            });
            return $ApiClass->responseOk([
                "response" => $response
            ]);

        }catch (\Exception $e){
            Log::info($e);
            return $ApiClass->responseError("失敗しました");
        }
    }

    /**
     * ロジフォン連絡先の一覧
     * @param  ApiClass  $ApiClass
     * @param  Request  $request
     * @return object|void
     */
    public function getCompanyFromBranchLP(ApiClass $ApiClass, Request $request)
    {
        try{
            $input = $request->branch_id;
            $LPCompanyBranch = LPCompanyBranch::find($input);
            return $ApiClass->responseOk(["id" => $LPCompanyBranch->company_id]);

        }catch (\Exception $e){
            Log::info($e);
            return $ApiClass->responseError("失敗しました");
        }
    }

     /**
     * 連絡先
     * @param  ApiClass  $ApiClass
     * @param  Request  $request
     * @return object|void
     */
    public function getCompanyBranch(ApiClass $ApiClass, Request $request)
    {
        try{
            $type = $request->type;
            $id= $request->_id;
            $LPCompanyBranch = $type==2?CompanyBranch::find($id):LPCompanyBranch::find($id);
            return $ApiClass->responseOk(["id" => $LPCompanyBranch]);

        }catch (\Exception $e){
            Log::info($e);
            return $ApiClass->responseError("失敗しました");
        }
    }

    /**
     * 連絡先
     * @param  ApiClass  $ApiClass
     * @param  Request  $request
     * @return object|void
     */
    public function saveCompanyBranch(ApiClass $ApiClass, Request $request)
    {
        try{
            DB::beginTransaction();
            $branch = $request->branch;
            $type = $branch['store_pos'];
            if($type==1){
                $LPCompanyBranch = LPCompanyBranch::firstOrNew(["id" => getVariable($branch, "id")]);
                $LPCompanyBranch->inputToModel($branch);
                $LPCompanyBranch->save();
            }else{
                $CompanyBranch = CompanyBranch::firstOrNew(["id" => getVariable($branch, "id")]);
                $CompanyBranch->inputToModel($branch);
                $CompanyBranch->save();
            }

            DB::commit();
            return $ApiClass->responseOk(["id" => $branch["id"]]);
        }catch (\Exception $e){
            Log::info($e);
            return $ApiClass->responseError($e->getMessage());
        }
    }

        /**
     * 連絡先
     * @param  ApiClass  $ApiClass
     * @param  Request  $request
     * @return object|void
     */
    public function uploadImage(ApiClass $ApiClass, Request $request)
    {
        try{

            // $branch = $request->branch;

            $request->validate([
                'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048' // Adjust validation rules as needed
            ]);

            // Store the uploaded image in the storage directory
            $storePath = "company";
            $imageName = "test3".".".$request->file('image')->extension();

            $imagePath = $request->file('image')->storeAs($storePath,$imageName,'public');

            return $ApiClass->responseOk(["id" => $imagePath]);
        }catch (\Exception $e){
            Log::info($e);
            return $ApiClass->responseError($e->getMessage());
        }
    }

    public function getFilesFromStorage(Request $request)
    {
        $store_pos = $request->store_pos;
        $directory = $request->directory;
        // 'public/company'; // Specify the directory path

        try {
            //code...
            $files = [];
            // Get list of files from the storage directory
            if($store_pos == "1"){
                $file_urls = Storage::files($directory);
                foreach ($file_urls as $key => $file_url) {
                    # code...
                    $files[] = str_replace('public','storage',$file_url);
                }
            }else{

            }
            return response()->json($files);
        } catch (\Exception $e) {
            //throw $th;
            return response($e->getMessage());
        }

    }

}
