<?php

namespace App\Libs\Common;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/*
*	Crudの共通処理
*/
class CrudClass{
    public $Model;
    public $request;

    /**
     * 一覧の取得
     */
    public function list($Model, $request) : array
    {
        if ($request->input("page") == -1){
            $Model = $Model
                ->get();
        }else{
            $Model = $Model
                ->getPage(50, intval($request->input("page")), '', $request->input(), "query");
        }


        if ($request->input("page") == -1) {
            $Model = $Model;
        }else{
            $Model["data"] = $Model["data"];
        }

        return $Model;

    }

    /**
     * 詳細の取得
     */
    public function get($Model, $request)
    {
        $Model = $Model->whereId($request->input("id"));

        return $Model->first();
    }

    /**
     * 削除　　
     */
    public function delete($Model, $request) : void
    {
        $this->deleteWhere["id"] = $request->input("id");

        $delete = $Model
            ::where($this->deleteWhere)->first();


        if ($delete){
            $delete->delete();
        }
    }

    /**
     * 値のチェック
     */
    public function check($request) : string
    {
        //エラーチェック
        $validator = $request->getValidator();
        $errorMessage = "";


        if ($validator->fails()) {

            // エラーの場合
            $errors = $validator->errors()->getMessages();

            foreach ($errors as $error){
                foreach ($error as $errorData){
                    $errorMessage.=$errorData . "<br />";
                }
            }
        }

        return $errorMessage;
    }

    /**
     * 値の保存
     */
    public function save($Model, $request, $types=[])
    {
        $this->saveWhere = ["id" => $request->input("id")];

        if (gettype($Model) == "string"){
            $Model = $Model::make()->newInstance();
        }

        $Model = $Model->firstOrNew(
            $this->saveWhere
        );

        if ($request->id){
            if (!$Model){
                return null;
            }
        }

        $Model->inputToModel($request->input(), $types);

        $Model->uploadFile($request->input());
        $Model->save();

        return $Model;
    }

    /**
     * ファイルのインポート
     * @param  string  $file
     * @param $ExcelClassPlus
     * @return void
     */
    public function import(string $file, $ExcelClassPlus)
    {
        $FileClass = new FileClass();


        list($mime, $base64) = $FileClass->base64Split($file);

        $contents = base64_decode($base64);
        $UploadClass = new UploadClass(null, "cache", $contents);
        $mime = $UploadClass->mimeType;


        if (strpos($mime, "spreadsheetml")){
            //一時アップ
            $UploadClass->upload();

            $ExcelClass = new ExcelClass();
            $ExcelClass->import($UploadClass->getFullPath(), $ExcelClassPlus);
        }

    }

}

