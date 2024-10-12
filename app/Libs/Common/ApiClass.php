<?php

namespace App\Libs\Common;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/*
*	APIの共通処理
*/
class ApiClass{
    /**
     * APIでエラーが発生した際のレスポンス
     * @param  string  $error
     * @param  array  $data
     * @return object
     */
    public function responseError(string $error, array $data = []) : object
    {

        $data["error"] = $error;

        $origin = "";

        if (isset($_SERVER['HTTP_ORIGIN'])){
            $origin = $_SERVER['HTTP_ORIGIN'];
        }

        return response()->json(array_merge(['result' => 0], $data), 200)
            ->header('Access-Control-Allow-Origin', $origin)
            ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'Content-Type')
            ->header('Access-Control-Allow-Credentials', 'true')
            ->header('Access-Control-Max-Age', '86400')
        ;
    }

    /**
     * APIで問題がない場合の際のレスポンス
     * @param  array  $data
     * @return object
     */
    public function responseOk(array $data = []) : object
    {
        $origin = "";
        if (isset($_SERVER['HTTP_ORIGIN'])){
            $origin = $_SERVER['HTTP_ORIGIN'];
        }

        return response()->json(array_merge(['result' => 1], $data), 200, [],JSON_BIGINT_AS_STRING )
            ->header('Access-Control-Allow-Origin', $origin)
            ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'Content-Type')
            ->header('Access-Control-Allow-Credentials', 'true')
            ->header('Access-Control-Max-Age', '86400')
        ;
    }

    /**
     * エラー関連の生成
     * @param  Request  $request
     * @return mixed
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function createValidateAll() : array
    {
        $FileClass = new FileClass(dirname(__FILE__) . "/../../Http/Requests");
        $dirs = $FileClass->getDirAll();

        $requests = [];

        if ($dirs){
            foreach ($dirs as $dir){
                list($dummy, $file) = explode("../../", $dir);


                $file = str_replace(".php", "", $file);
                $key = basename($file);
                $file = str_replace("/", "\\", $file);

                app()->bind('RequestClass', "\App\\" . $file);
                $request = app()->make("RequestClass");


                $requests[$key]["rules"] = $request->rules();
                $requests[$key]["attributes"] = $request->attributes();

            }
        }

        return $requests;
    }
}

