<?php

namespace App\Http\Controllers;

use App\Libs\Common\ApiClass;
use App\Models\Area;
use App\Models\LogiPhone\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\JsonResponse;
use Illuminate\Support\Facades\Auth;

/**
 *	エリア取得
 */
class SettingController extends Controller
{

    /**
     *
     * @param  ApiClass  $ApiClass
     * @return JsonResponse
     */

    public function getCount(ApiClass $ApiClass, Request $request)
    {
        try {
            $user_id = Auth::user()->id;
            // $user_id = 1;
            $unit_count = Setting::where('auth_id', $user_id)->first() ? Setting::where('auth_id', $user_id)->first()->unit_count : 50;

            return $ApiClass->responseOk([
                "unit_count" => $unit_count,
            ]);
        } catch (\Exception $exception) {

            return $ApiClass->responseError($exception->getMessage());
        }
    }

    /**
     *
     * @param  ApiClass  $ApiClass
     * @return JsonResponse
     */

    public function saveCount(ApiClass $ApiClass, Request $request)
    {
        try {
            $user_id = Auth::user()->id;
            // $user_id = 1;
            // return $user_id;
            $setting = Setting::where('auth_id', $user_id)->first() ?? new Setting();
            $setting->auth_id = $user_id;
            $setting->unit_count = $request->unit_count;
            $setting->save();

            return $ApiClass->responseOk([
                "unit_count" => $setting->unit_count,
            ]);
        } catch (\Exception $exception) {

            return $ApiClass->responseError($exception->getMessage());
        }
    }
}
