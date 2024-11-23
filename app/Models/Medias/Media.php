<?php

namespace App\Models\Medias;

use App\Libs\Common\ModelClass;
use App\Libs\Common\UploadClass;
use Illuminate\Database\Eloquent\Builder;
use Imagick;

class Media extends ModelClass
{
    public function upload(string $uploadType, string $value, string $origin="")
    {

        if (strpos($value, ".")){
            //拡張子ありのままのデータの場合は処理を行わない(更新じゃないため)
            return $value;
        }

        if ($value){

            $data = explode("data:", $value);
            list($mime, $base64) = explode(";base64,", $data[1]);



            //内容がある場合
            $UploadClass = new UploadClass($this->path, $uploadType, base64_decode($base64));

            $this->path = $UploadClass->uploadFile();

            $this->mime = $mime;



            //PDFの場合はサムネイルを生成する
            if ($this->mime == "application/pdf"){
                $imagick = new Imagick();

                $result_flag1 = $imagick->readimage($UploadClass->getFullPath());// $file_pathはPDFの場所1ページ目のみ読み込む
                $result_flag2 = $imagick->setIteratorIndex(0);// 1ページ目
                $result_flag3 = $imagick->setImageFormat('png');
                $image_info = $imagick->getImageGeometry();
                $result_flag5 = $image_info['width'] <= $image_info['height']
                    ? $imagick->resizeImage(0, 470, Imagick::FILTER_LANCZOS, 1)// 縦長 アスペクト比維持してリサイズ
                    : $imagick->resizeImage(440, 0, Imagick::FILTER_LANCZOS, 1);// 横長 アスペクト比維持してリサイズ

                $this->thumbnail = str_replace(".pdf", ".png", $UploadClass->fileName);
                $thumbnail = str_replace(".pdf", ".png", $UploadClass->getFullPath());
                $result_flag6 = $imagick->writeimage($thumbnail);
                $imagick->clear();
            }else{
                $this->thumbnail = $this->path;
            }

            $this->upload_type = $uploadType;

            $this->origin = $origin;
            $this->save();


            return $this->path;
        }
    }

    public function scopeWhereFile(Builder $builder, string $tableName, $tableId, string $type)
    {
        $builder->whereTableName($tableName)
            ->whereTableId($tableId)
            ->whereType($type);

    }
}
