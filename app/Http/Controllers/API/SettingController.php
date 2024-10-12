<?php

namespace App\Http\Controllers\API;

use App\Libs\Common\ApiClass;
use App\Models\Area;
use App\Models\LogiPhone\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\JsonResponse;

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
            $unit_count = Setting::first()?Setting::first()->page_unit : null;

            return $ApiClass->responseOk([
                                            "unit_count" => $unit_count,
                                         ]);
        }catch (\Exception $exception) {

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
            $setting = Setting::first()??null;
            $setting->page_unit= $request->unit_count;
            $setting->save();

            return $ApiClass->responseOk([
                                             "unit_count" => $setting->page_unit,
                                          ]);
        }catch (\Exception $exception) {

             return $ApiClass->responseError($exception->getMessage());
        }
    }
}
