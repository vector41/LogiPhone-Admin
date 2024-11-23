<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\Controller;
use App\Libs\Common\ApiClass;
use App\Libs\GroupClass;
use App\Models\LogiPhone\LPCompany;
use App\Models\LogiPhone\LPCompanyBranch;
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

    public function getAllUsers(Request $request, ApiClass $apiClass)
    {
        try {
            $allUsers = CompanyEmployee::all();
            // return $apiClass->responseOk(["res" => $allUsers]);
            return $allUsers;
        } catch (\Exception $e) {
        }
    }
    /**
     * 一覧の取得
     */
    public function list(ApiClass $ApiClass, Request $request)
    {
        try {
            //code...
            ini_set('memory_limit', '2048M');
            // $user = Auth::user();
            $user_id = 11;
            $user_company_id = CompanyEmployee::find($user_id)->company_id;
            $users_from_company = CompanyEmployee::where("company_id", $user_company_id)->select('id')->get();
            $user_ids = $users_from_company->map(function ($user) {
                return $user->id;
            })->toArray();

            $datas = CompanyBranch::with(["company"])
                ->where(function ($builder) use ($user_ids) {
                    $builder->WhereIn("company_branches.updated_id", $user_ids);
                })->orderBy("company_branches.id", "asc")->limit(50)->get();
            $response_2 = $datas->map(function ($branch) {
                return [
                    'id' => $branch->id,
                    'store_pos' => 2,
                    'prefecture' => $branch->prefecture,
                    'tel' => $branch->tel,
                    'company_name_full_short' => $branch->company->company_name_full_short,
                ];
            });

            $datas = LPCompanyBranch::with(["company"])->orderBy("company_branches.id", "DESC")
                ->where(function ($builder) use ($user_ids) {
                    $builder->orWhereIn("company_branches.updated_id", $user_ids);
                    $builder->orWhereIn("company_branches.created_id", $user_ids);
                })->limit(50)->get();
            $response_1 = $datas->map(function ($branch) {
                return [
                    'id' => $branch->id,
                    'store_pos' => 1,
                    'prefecture' => $branch->prefecture,
                    'tel' => $branch->tel,
                    'company_name_full_short' => $branch->company->company_name_full_short,
                ];
            });
            $response = $response_1->merge($response_2);
            // response()->json($response->toArray());
            return $ApiClass->responseOk([
                "response" => $response,
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

        if ($Company) {
            $response = $Company;
            // $response->children->append(["car_request_sites", "document"]);
        } else {
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
        try {
            DB::beginTransaction();

            $input = $request->input();
            $Company = Company::firstOrNew(["id" => getVariable($input, "id")]);
            $Company->inputToModel($input);

            if (empty($input["id"])) {
                //ここで作成された物はステータスを2にする
                $Company->supplier_status = 2;
            }

            $Company->save();
            // $Company->uploadFile($input);

            $Company->organizations = getVariable($input, "organizations", []);
            $Company->permits = getVariable($input, "permits", []);

            if (!$input["is_company_branch"]) {
                $input["children"][0]["branch_name"] = "本社";
                $input["children"][0]["is_main_office"] = 1;
            }

            if (!empty($input["children"])) {
                foreach ($input["children"] as $branch) {
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
        } catch (\Exception $e) {
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
        try {
            DB::beginTransaction();
            LPCompanyBranch::whereIn("id", $request->ids)->delete();
            DB::commit();

            return $ApiClass->responseOk([]);
        } catch (\Exception $e) {
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
        try {
            DB::beginTransaction();
            $input = $request->company;

            $LPCompany = LPCompany::firstOrNew(["id" => getVariable($input, "id")]);
            $LPCompany->inputToModel($input);

            $LPCompany->save();
            // $LPCompany->uploadFile($input);

            LPCompanyBranch::where('company_id', $LPCompany->id)->delete();
            if (!$input["is_company_branch"]) {
                $input["children"][0]["branch_name"] = "本社";
                $input["children"][0]["is_main_office"] = 1;
            }
            if (!empty($input["children"])) {
                foreach ($input["children"] as $branch) {
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
        } catch (\Exception $e) {
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
        try {
            $datas = LPCompanyBranch::with(["company"])->orderBy("company_branches.id", "DESC")->limit(300)->get();
            $response = $datas->map(function ($branch) {
                return [
                    'id' => $branch->id,
                    'type' => 1,
                    'branch_name' => $branch->branch_name,
                    'prefecture' => $branch->prefecture,
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
        } catch (\Exception $e) {
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
        try {
            $input = $request->branch_id;
            $LPCompanyBranch = LPCompanyBranch::find($input);
            return $ApiClass->responseOk(["id" => $LPCompanyBranch->company_id]);
        } catch (\Exception $e) {
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
        try {
            $type = $request->type;
            $id = $request->_id;
            $LPCompanyBranch = $type == 2 ? CompanyBranch::find($id) : LPCompanyBranch::find($id);
            return $ApiClass->responseOk(["id" => $LPCompanyBranch]);
        } catch (\Exception $e) {
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
        try {
            DB::beginTransaction();
            $branch = $request->branch;
            $type = $branch['store_pos'];
            if ($type == 1) {
                $LPCompanyBranch = LPCompanyBranch::firstOrNew(["id" => getVariable($branch, "id")]);
                $LPCompanyBranch->inputToModel($branch);
                $LPCompanyBranch->save();
            } else {
                $CompanyBranch = CompanyBranch::firstOrNew(["id" => getVariable($branch, "id")]);
                $CompanyBranch->inputToModel($branch);
                $CompanyBranch->save();
            }

            DB::commit();
            return $ApiClass->responseOk(["id" => $branch["id"]]);
        } catch (\Exception $e) {
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
        try {

            // $branch = $request->branch;
            $request->validate([
                'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048' // Adjust validation rules as needed
            ]);

            // Store the uploaded image in the storage directory
            $storePath = "company";
            $imageName = "test3" . "." . $request->file('image')->extension();

            $imagePath = $request->file('image')->storeAs($storePath, $imageName, 'public');

            return $ApiClass->responseOk(["id" => $imagePath]);
        } catch (\Exception $e) {
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
            if ($store_pos == "1") {
                $file_urls = Storage::files($directory);
                foreach ($file_urls as $key => $file_url) {
                    # code...
                    $files[] = str_replace('public', 'storage', $file_url);
                }
            } else {
            }
            return response()->json($files);
        } catch (\Exception $e) {
            //throw $th;
            return response($e->getMessage());
        }
    }
}
