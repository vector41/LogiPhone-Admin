<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;


/**
 * デフォルトの日時を取得
 * @param  string  $type   種類
 * @param  string  $date   日時
 * @return string　日時の文字
 */
function dateDefault(string $type, ?string $date) : string
{
    if (!$date){
        return "-";
    }

    if ($type == "date"){
        return datetime("Y/m/d", $date);
    }
}

/**
 * 日時データの整形
 * @param $format
 * @param $datetime
 * @param  bool  $isAm00
 * @return string 日時の文字
 */
function datetime($format, $datetime, $isAm00=false) : string
{
    if ($isAm00){
        list($date1, $date2) = explode(':', $datetime);
        if ($date1 == '00'){
            return 'AM 00:' . $date2;
        }
    }
    $result = date($format, strtotime($datetime));

    return $result;
}


/**
 * サマリーの表示
 * @param $str  文字
 * @param  int  $num    制限数
 * @return string 文字
 */
function out($str, $num=40) : string
{
    $str = str_replace("\\n", "", $str);
    $str = strip_tags($str);

    if ($num){
        $str = mb_strimwidth($str, 0, $num, '...', 'utf-8');
    }

    return $str;
}

/**
 * 自分のドメイン取得
 * @return string
 */
function getMyURL() : string
{
    return (empty($_SERVER['HTTPS']) ? 'http://' : 'https://').$_SERVER['HTTP_HOST'];
}
/**
 * ファイルが存在するかの確認
 * @param  string  $fileName
 * @param  string  $uploadType
 * @return bool
 */
function isFile(string $fileName, string $uploadType) : bool
{
    $disk = Storage::disk($uploadType);
    $fullName = $disk->path($fileName);

    if (file_exists($fullName)){
        return true;
    }

    return false;
}

/**
 * 画像のアドレスの取得
 * @param  string  $fileName    ファイル名
 * @param  string  $uploadType  アップロード種類
 * @param  string  $size        サイズ
 * @return string
 */
function getImageUrl(?string $fileName, string $uploadType, string $size="thumbnail") : string
{

    if (!$fileName)
    {
        //画像情報がない場合
        $disk = Storage::disk("public");

        return $disk->url("/assets/images/noimage.png");
    }

    if (!isFile($fileName, $uploadType)){
        //ファイル名に入っているのがバイナリの場合は画像のツールを使い画像のURL取得
        $ImageClass = new App\Libs\Common\ImageClass("", base64_decode($fileName));

        if (!$ImageClass->isOk()){
            return "";
        }

        $ThumClass = $ImageClass->scale("thumbnail");

        return "data:" . $ThumClass->mime . ";base64," . base64_encode($ThumClass->getBinary());
    }else{
        return  route("api.image",[$uploadType, $size, $fileName]);
    }
}

/**
 * デバック状態又は指定IP以外は表示させない
 * @param $variable 変数
 * @param  string  $ip  $ipを指定する場合
 */
function debug($variable, string $ip="")
{
    if (env("APP_ENV") == "local") {
        echo "<script>document.write('<script src=\"http://' + (location.host || 'localhost').split(':')[0] + ':35729/livereload.js?snipver=1\"></' + 'script>')</script>";
        //デバック状態
        dd($variable);
    }
}

/**
 * グローバルな変数の所持
 * @param  string  $key
 * @param $variable
 */
function setGlobal(string $key, $variable)
{
    $GLOBALS["globals"][$key] = $variable;
}

/**
 * グローバルな変数の取得
 * @param  string  $key
 * @param $variable
 * @return mixed|null
 */
function getGlobal(string $key)
{
    if (isset($GLOBALS["globals"][$key])){
        return $GLOBALS["globals"][$key];
    }

    return null;
}

/**
 * 多次元配列のコンフィグをkeyvalue形式
 * @param  array  $data
 * @param  string  $value
 * @return array
 */
function changeConfigKeyValue(array $data, string $value)
{
    $result = [];


    foreach ($data as $key => $values){
        $result[$key] = $values[$value];
    }

    return $result;
}


function getVariable($data, $key, $default="")
{
    if (isset($data[$key])){
        return $data[$key];
    }

    return $default;
}

function arraySearchId($data, $id, $type="id") : array
{

    $columns = array_column( $data, $type);

    $search = array_search($id, $columns);

    if (strlen($search)){
        return $data[$search];
    }else{
        return [];
    }
}

function configSearchKey(string $configName, $key, string $type="id")
{   
    $configs = config($configName);
    $array = array_column($configs, $type);
    $result = getVariable($configs, array_search($key, $array));

    return $result;
}

function routeBase(string $url, string $controller)
{
    $modes = ["list" => "get", "all" => "get", "get" => "get", "detail" => "get", "save" => "any", "sort" => "post", "destroy" => "post", "select" => "get", "import" => "post", "total" => "get"];

    foreach ($modes as $key => $mode){
        Route::$mode($url . "/" . $key, $controller . "@" . $key);
    }
}

function changeGroup($data, string $type){
    $result = [];


    foreach ($data as $value){
        $result[$value[$type]] = $value;
    }

    return $result;
}

?>
