<?php

namespace App\Http\Controllers;

use App\Libs\Common\ApiClass;
use App\Models\Area;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
*	エリア取得
*/
class AreaController extends Controller
{

    /**
     * 都道府県市区群の取得
     * @param  ApiClass  $ApiClass
     * @return JsonResponse
     */
    public function index(ApiClass $ApiClass) : JsonResponse
    {
        try {
            //都道府県取得
            $prefecturesDb = Area::groupPrefectures()->get();

            ini_set('memory_limit', '256M');
            foreach ($prefecturesDb as $prefecture){
                $prefectures[] = ["id" => $prefecture->prefecture, "value" => $prefecture->prefecture_name];
            }

            $cityDb = Area::groupCity()->get();

            $cities = [];
            $double = [];

            foreach ($cityDb as $city){
                if (!isset($double[$city->city])){
                    $cities[] = ["id" => intval($city->city), "prefecture" => intval($city->prefecture), "value" => $city->city_name];
                    $double[$city->city] = true;
                }
            }

            return $ApiClass->responseOk([
                     "prefectures" => $prefectures,
                     "cities" => $cities,
            ]);
        }catch (\Exception $exception) {
            Log::info($exception);
            return $ApiClass->responseError("住所データの取得失敗");
        }
    }

    /**
     * 郵便番号で都道府県市区群の取得
     * @param  ApiClass  $ApiClass
     * @param  Request  $request
     * @return object
     */

    public function zip(ApiClass $ApiClass, Request $request)
    {
        try {
            $area = Area::where("zip", $request->input("zip"))->first();

            return $ApiClass->responseOk([
                                             "area" => $area,
                                         ]);
        }catch (\Exception $exception) {

            return $ApiClass->responseError(config("customs.error.area.zip"));
        }

    }

}
