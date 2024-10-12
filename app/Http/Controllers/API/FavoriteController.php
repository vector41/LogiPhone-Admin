<?php

namespace App\Http\Controllers\API;

use App\Libs\Common\ApiClass;
use App\Models\LogiPhone\Favorite;
use App\Models\LogiPhone\LPCompanyBranch;
use App\Models\LogiPhone\LPCompanyEmployee;
use App\Models\LogiScope\CompanyBranch;
use App\Models\LogiScope\CompanyEmployee;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;

class FavoriteController extends Controller
{

        /**
     * Update the user's profile information.
     */
    public function list(ApiClass $ApiClass, Request $request)
    {
        try {

            $favorites = Favorite::where('employeer',$request->employeer)->orderBy('id')->get();
            $favorite_datas = $favorites->map(function ($fav) {
                $favorite_detail = null;
                if($fav->main_cat==1&&$fav->emp_pos==1){
                    $favorite_detail = LPCompanyBranch::with('company')->find($fav->fovoriter);
                }else if($fav->main_cat==1&&$fav->emp_pos==2){
                    $favorite_detail = CompanyBranch::with('company')->find($fav->fovoriter);
                }else if($fav->main_cat==2&&$fav->emp_pos==1){
                    $favorite_detail = LPCompanyEmployee::find($fav->fovoriter);
                }else{
                    $favorite_detail = CompanyEmployee::find($fav->fovoriter);
                }
                return [
                    'id'=>$favorite_detail->id,
                    'store_pos'=>$fav->fav_pos,
                    'main_cat'=>$fav->main_cat,
                    'name'=>$fav->main_cat==1?$favorite_detail->company->company_name_full_short:$favorite_detail->person_name_second.$favorite_detail->person_name_first,
                    'tel'=>$fav->main_cat==1?$favorite_detail->tel:$favorite_detail->tel1,
                ];
            });

            return response()->json($favorite_datas);
        } catch (\Exception $e) {
            Log::info($e);
            return $ApiClass->responseError($e->getMessage());
        }
    }

    /**
     * Update the user's profile information.
     */
    public function save(ApiClass $ApiClass, Request $request)
    {
        try {

            $favorite = Favorite::updateOrCreate([
                'emp_pos' => $request->emp_pos,
                'employeer' => $request->employeer,
                'fav_pos' => $request->fav_pos,
                'main_cat' => $request->main_cat,
                'fovoriter' => $request->fovoriter,
            ]);

            $favorites = Favorite::where('employeer',$request->employeer)->orderBy('id')->get();
            $favorite_datas = $favorites->map(function ($fav) {
                $favorite_detail = null;
                if($fav->main_cat==1&&$fav->emp_pos==1){
                    $favorite_detail = LPCompanyBranch::with('company')->find($fav->fovoriter);
                }else if($fav->main_cat==1&&$fav->emp_pos==2){
                    $favorite_detail = CompanyBranch::with('company')->find($fav->fovoriter);
                }else if($fav->main_cat==2&&$fav->emp_pos==1){
                    $favorite_detail = LPCompanyEmployee::find($fav->fovoriter);
                }else{
                    $favorite_detail = CompanyEmployee::find($fav->fovoriter);
                }
                return [
                    'id'=>$favorite_detail->id,
                    'store_pos'=>$fav->fav_pos,
                    'main_cat'=>$fav->main_cat,
                    'name'=>$fav->main_cat==1?$favorite_detail->company->company_name_full_short:$favorite_detail->person_name_second.$favorite_detail->person_name_first,
                    'tel'=>$fav->main_cat==1?$favorite_detail->tel:$favorite_detail->tel1,
                ];
            });

            return response()->json($favorite_datas);
        } catch (\Exception $e) {
            Log::info($e);
            return $ApiClass->responseError($e->getMessage());
        }
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current-password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
