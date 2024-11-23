<?php

namespace App\Libs\Common;

use App\Models\Area;
use App\Models\Medias\Media;
use DateTimeZone;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Nette\Utils\DateTime;

class ModelClass extends Authenticatable
{

    protected $guarded = ['id'];
    public array $files = [];
    public $otherGetter = [];

    function __construct($attributes = [])
    {
        // parent::__construct($attributes);

        /**
         * 画像をアクセに登録登録
         */
        if (!empty($this->files)) {
            foreach ($this->files as $file) {
                $this->otherGetter[$file] = function () use ($file) {
                    $medias = Media::whereFile($this->getTable(), $this->id, $file)
                        ->select("path as file", "origin as file_origin")
                        ->get();

                    return $medias;
                };
            }
        }
    }

    /**
     * mutateAttributeの書き換え
     *
     * @param string $key
     * @param mixed  $value
     * @return void
     */
    protected function mutateAttribute($key, $value)
    {
        if (isset($this->otherGetter[$key])) {
            return $this->otherGetter[$key]();
        }

        return parent::mutateAttribute($key, $value);
    }

    /**
     *  パスワード形式に保存
     */
    public function changePassword()
    {

        if (!empty($this->password)) {
            $this->password = bcrypt($this->password);
        } else {
            unset($this->password);
        }
    }

    /**
     *  住所形式に保存
     */
    public function changeAddress($prefecture, $city, $other, $building = "")
    {

        $Area = Area::whereCity($city)->first();

        $city = "";
        if ($Area) {
            $city = $Area->city_name;
        }

        return getVariable(configSearchKey("customs.prefectures", $prefecture), "value") . $city . $other . $building;
    }

    /**
     * ファイルをアップロードとファイルの更新
     */
    public function uploadFile(array $values): void
    {

        if (isset($this->files)) {
            foreach ($this->files as $file) {
                if (isset($values[$file])) {
                    if (!is_array($values[$file])) {
                        //複数ではない場合
                        $valueFiles[] = ["file" => $values[$file], "file_origin" => $values[$file . "_origin"]];
                    } else {
                        $valueFiles = $values[$file];
                    }



                    //存在しないものは削除とする
                    $Media = Media::where("type", $file)->where("table_name", $this->getTable())
                        ->where("table_id", $this->id);

                    $Medias = $Media->where(function (Builder $builder) use ($valueFiles) {
                        foreach ($valueFiles as $valueFile) {
                            $builder->where("path", "<>", $valueFile["file"]);
                        }
                    })->get();

                    foreach ($Medias as $Media) {
                        $UploadClass = new UploadClass($Media->path, $this->uploadType, "");
                        $UploadClass->delete();
                        $Media->delete();
                    }


                    //追加するものは追加する
                    foreach ($valueFiles as $valueFile) {
                        //メディアモデルの生成か変更
                        $key = $file . "_origin";
                        $Media = Media::firstOrNew([
                            "path" => $this->$file,
                            "type" => $file,
                            "table_name" => $this->getTable(),
                            "table_id" => $this->id,
                        ]);

                        $Media->type = $file;
                        $Media->table_name = $this->getTable();
                        $Media->table_id = $this->id;


                        $Media->upload($this->uploadType, $valueFile["file"], getVariable($valueFile, "file_origin"));
                    }
                }
            }
        }
    }

    /**
     * 存在しないものは全て削除
     * @param  Builder  $builder
     * @param  array  $targets
     * @param  string  $key
     */
    public function scopeNotDelete(Builder $builder, array $targets, string $key = "id")
    {
        $builder->where(function (Builder $builder) use ($targets, $key) {
            foreach ($targets as $target) {
                if ($target) {
                    $builder->where($key, "!=", getVariable($target, $key, 0));
                }
            }
        });

        $builder->delete();
    }


    /**
     * ページャー
     * @param $query
     * @param  int  $page
     * @param  int  $current
     * @param  string  $name
     * @param  array  $parameters
     * @param  string  $type
     * @return array
     */
    public function scopeGetPage($query, int $page, int $current, string $name, array $parameters = [], string $type = "slash")
    {
        if (!$current) {
            $current = 1;
        }

        $min = ($current - 1) * $page;
        $max = ($min) + $page;


        //総個数
        $total = $query
            ->distinct($this->getTable() . ".id")
            ->count();


        //実際のデータ

        $data  = $query
            // ->groupBy($this->getTable() . ".id")
            ->offset($min)
            ->limit($page)
            ->get();

        //ナビゲーションの数
        $countNavi = ceil($total / $page);


        //アドレスの生成
        if ($type != "query") {
            $url = "/" . $name;

            if ($parameters) {
                foreach ($parameters as $parameter) {
                    $url .= "/" . $parameter;
                }
            }
        } else {
            $url = "?" . http_build_query($parameters);
        }


        $maxCount = $max;
        if ($max > $total) {
            $maxCount = $total;
        }


        return [
            "navigation" => [
                "number" => $countNavi,
                "current" => $current,
                "total" => $total,
                "url" => $url,
                "type" => $type,
                "min" => $min + 1,
                "max" => $maxCount,
            ],
            "data" => $data,
        ];
    }

    /**
     * whereHasとwithの検索と取得
     * @param $query
     * @param $relation
     * @param $constraint
     * @return mixed
     */
    public function scopeWithWhereHas($query, $relation, $constraint)
    {
        $query
            ->whereHas($relation, $constraint)
            ->with([$relation => $constraint]);

        return $query;
    }
    public function scopeWithWhereNotHas($query, $relation, $constraint)
    {
        $query
            ->whereDoesntHave($relation, $constraint)
            ->with([$relation => $constraint]);

        return $query;
    }

    /**
     * inputからmodelの値の整形
     */
    public function inputToModel($inputs, $types = [])
    {
        $columns = DB::connection()->getSchemaBuilder()->getColumnListing($this->getTable());

        foreach ($inputs as $key => $input) {
            if (in_array($key, $this->files)) {
                //画像は対象外
                unset($inputs[$key]);
            }
        }

        if ($types) {
            $columns = array_flip($types);
        } else {
            $columns = array_flip($columns);
        }

        foreach ($inputs as $key => $value) {
            if (isset($columns[$key])) {
                if (is_array($value)) {
                    //配列の場合はJSONに
                    foreach ($value as $key2 => $value2) {
                        if (is_numeric($value2)) {
                            $value[$key2] = strval($value2);
                        }
                    }
                    if ($value) {
                        $this->$key = json_encode($value);
                    }
                } else {
                    $this->$key = $value;

                    if ($key == "password") {
                        //パスワードの場合は暗号化
                        //$this->changePassword();
                    }

                    if (($value == "0-00-00") || ($value == "0000-00-00")) {
                        $this->$key = null;
                    }
                }
            }
        }
    }

    /**
     * modelの値の整形
     */
    public function surgeryModel()
    {
        $columns = DB::connection()->getSchemaBuilder()->getColumnListing($this->getTable());

        $columns = array_flip($columns);

        foreach ($this->toArray() as $key => $value) {
            if (!isset($columns[$key])) {
                unset($this->$key);
            } else {
                if (is_array($value)) {
                    //配列の場合はJSONに
                    $this->$key = json_encode($value);
                }
            }
        }
    }

    /**
     * 複数データの更新
     */
    public function updateChildArray($model, array $data, $value, $wheres = [])
    {
        $parentId = str_replace("\\", '', Str::snake(class_basename($this))) . "_id";

        $delete = $model::make()->newInstance();
        $delete = $delete->where($parentId, $this->id);


        if ($wheres) {
            foreach ($wheres as $key => $where) {
                $delete = $delete->where($key, $where);
            }
        }
        if ($data) {
            $delete->notDelete(array_column($data, "id"));
        } else {
            //空の場合は全削除
            $delete->delete();
        }

        foreach ($data as $item) {
            $update = $model::make()->newInstance();
            $update = $update
                ->where($parentId, $this->id)
                ->firstOrNew(["id" => $item]);

            $update->$parentId = $this->id;

            $isEmpty = true;

            if (is_array($value)) {
                foreach ($value as $key) {
                    if (!empty($item[$key])) {
                        $isEmpty = false;
                        $update->$key = $item[$key];
                    }
                }
            } else {
                $update->$value = $item;
                $isEmpty = false;
            }

            if (!$isEmpty) {
                $update->save();
            }
        }
    }


    public function setCreateUpdateUserAttribute($userId)
    {
        if (!$this->id) {
            $this->created_id = $userId;
        } else {
            $this->updated_id = $userId;
        }
    }

    /**
     * 新しいEloquentCollectionインスタンスの作成
     *
     * @param  array  $models
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function newCollection(array $models = [])
    {
        return new ModelCollection($models);
    }

    public function getCreatedAtAttribute($value)
    {
        $t = new DateTime($value);
        $t->setTimeZone(new DateTimeZone('Asia/Tokyo'));
        return $t->format('Y-m-d H:i:s');
    }
    public function getUpdatedAtAttribute($value)
    {
        $t = new DateTime($value);
        $t->setTimeZone(new DateTimeZone('Asia/Tokyo'));
        return $t->format('Y-m-d H:i:s');
    }

    /*public function medias()
        {
            foreach ($this->files as $file){
                $medias = Media::whereFile($this->getTable(), $this->id, $file)
                    ->select("path as file", "origin as file_origin")
                    ->get();

                $this->attributes[$file] = $medias;
            }

            return $this;
        }*/
}
