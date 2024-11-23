<?php

namespace App\Libs\Common;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/*
*	並替関連の処理
*/
class SortClass{
    public $key = "sort";

    public function maxSort($myModel, $Model)
    {
        if ($myModel->id){
            return $myModel;
        }

        $Model = $Model->orderBy($this->key, "DESC")->first();

        $key = $this->key;
        if ($Model){
            $myModel->$key = $Model->$key;
        }else{
            $myModel->$key = 1;
        }

        return $myModel;
    }
}

