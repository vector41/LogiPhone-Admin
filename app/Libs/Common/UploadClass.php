<?php

namespace App\Libs\Common;

/* アップロードファイル管理クラス */

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\Storage;

class UploadClass
{
    public $fileName = "";
    public string $uploadType = "";
    public string $binary = "";
    public string $mimeType = "";

    /**
     * 初期設定
     * @param  string|null  $fileName  ファイル名
     * @param  string  $uploadType  アップロードの種類
     * @param  string  $binary  アップする予定のファイル
     */
    public function __construct(?string $fileName, string $uploadType, string $binary="", string $prefix="")
    {

        $this->fileName = $fileName;
        $this->uploadType = $this->getType($uploadType);
        $this->binary = $binary;

        //拡張子取得
        $extension = $this->extension();

        if (!$fileName){
            //空の場合はランダムファイル名
            $this->fileName = $prefix . substr(str_shuffle('1234567890abcdefghijklmnopqrstuvwxyz'), 0, 30);


            if ($extension){
                $this->fileName = $this->fileName . "." . $extension;
            }
        }

    }

    public function getFullPath() : string
    {
        $diskApp = Storage::disk($this->uploadType);
        return $diskApp->path($this->fileName);
    }

    public function extension()
    {
        $finfo = new \finfo(FILEINFO_MIME);
        $mime = $finfo->buffer($this->binary);

        list($this->mimeType, $char) = explode(";", $mime);

        $handle = explode("\n", config("mime"));
        foreach ($handle as $line){
            $line = trim($line, " \t\r\n;");

            if (empty($line) || '#' === $line[0]) {
                continue;
            }

            $line = str_replace("\t", ' ', strtolower($line));
            $split = explode(' ', $line);

            $type = end($split);
            if(!empty($split) && $type === $this->mimeType) {
                $extension = array_values(array_filter($split));
                break;
            }
        }



        if (isset($extension[0])){
            return $extension[0];
        }else{
            return "unknow";
        }
    }

    /**
     * 外部ストレージか内部ストレージか
     * @param $type 内部ストレージの種類
     * @return string
     */
    public function getType($type) : string{
        if (env("IS_STORAGE")){
            return "s3";
        }

        return $type;
    }

    /**
     * バイナリデータで保存する
     */
    public function upload() : void
    {
        $diskApp = Storage::disk($this->uploadType);
        $diskApp->put($this->fileName, $this->binary);
    }

    /**
     * サムネイルの作成
     * @return void
     */
    public function createThumbnail() : void
    {
        $diskApp = Storage::disk($this->uploadType);

        if ($this->binary){
            $diskApp->put($this->fileName, $this->binary);
        }

        if (!empty(env("WEBP"))){

            //Webpも作成
            $ImageClass = new ImageClass($diskApp->path($this->fileName));
            $ImageClass->change("image/webp");

            $dir = $diskApp->path("") . "webp/";
            if (!file_exists($dir)){
                mkdir($dir);
                chmod($dir, 0777);
            }

            $ImageClass->save($dir . $this->fileName . ".webp");
        }

        $diskApp = Storage::disk($this->uploadType);
        $dir = $diskApp->path($this->fileName);
        $dir = str_replace($this->fileName, "", $dir);



        $Image = new ImageClass($diskApp->path($this->fileName));



        foreach ($Image->sizes as $key => $size){

            if (!file_exists($dir . $key)){
                mkdir($dir . $key);
                chmod($dir . $key, 0777);
            }

            $ThumbnailClass = $Image->scale($key);
            $ThumbnailClass->save($dir . $key . '/' . $this->fileName);

            if (!empty(env("WEBP"))){
                //Webpも作成
                $ThumbnailClass->change("image/webp");

                $dirWebp = $diskApp->path("") . $key . "/webp/";

                if (!file_exists($dirWebp)){
                    mkdir($dirWebp);

                    chmod($dirWebp, 0777);
                }

                $ThumbnailClass->save($dirWebp . $this->fileName . ".webp");
            }
        }
    }

    /**
     * ファイルを指定しレスポンスを返す
     * @param string $size  サイズ
     * @return mixed
     * @throws FileNotFoundException
     */
    public function view(string $size=""){
        $diskApp = Storage::disk($this->getType($this->uploadType));

        $fileName = $this->fileName;

        if ($size){
            $fileName = "/" . $size . "/" . $this->fileName;
        }

        $fullPath = $diskApp->path($fileName);


        //該当画像がない場合
        if (!file_exists($fullPath)){
            $this->createThumbnail();
        }


        $this->contents = $diskApp->get($fileName);
        $this->mimeType = $diskApp->mimeType($fileName);

        $response = $diskApp->response($fileName);

        return $response;
    }

    function delete()
    {
        $diskApp = Storage::disk($this->uploadType);

        $path = $diskApp->path("");

        foreach (config("image")["sizes"] as $key => $size){
            if (file_exists($path . $key . '/' . $this->fileName)){
                unlink($path . $key . '/' . $this->fileName);

                if (!empty(env("WEBP"))){
                    if (file_exists($path . $key . '/webp/' . $this->fileName.".webp")) {
                        unlink($path.$key.'/webp/'.$this->fileName.".webp");
                    }
                }
            }
        }

        if (file_exists($path.$this->fileName)) {
            unlink($path.$this->fileName);

            if (file_exists($path."webp/".$this->fileName.".webp")) {
                unlink($path."webp/".$this->fileName.".webp");
            }
        }
    }

    /**
     * アップロード共通処理
     */
    public function uploadFile() : string
    {
        //既にファイルがある場合は消去する
        $fileName = Storage::disk($this->uploadType)->path($this->fileName);


        if (file_exists($fileName)){
            $UploadClass = new UploadClass($this->fileName, $this->uploadType);
            $UploadClass->delete();
        }

        $this->upload();


        if (
            ($this->mimeType == "image/png") ||
            ($this->mimeType == "image/gif") ||
            ($this->mimeType == "image/jpeg")
        ){
            //画像はサムネイルも作成
            $this->createThumbnail();
        }


        return $this->fileName;
    }

}
