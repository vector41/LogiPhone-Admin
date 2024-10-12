<?php

namespace App\Libs\Common;



/*
*	日時関連の共通処理
*/
class DateClass{

    /**
     * 翌月を取得(月末処理)
     * @param  string  $date
     * @return string
     */
    public function getNextDate(string $date) : string
    {
        //現在の日付取得
        $day = date("d", strtotime($date));

        //翌月末の情報取得
        $time = strtotime(date("Y-m-t 23:59:59", strtotime($date))) + 3600;

        if ($day > date("t", $time)){
            //その日の翌月末が存在しないため変更する
            $day = date("t", $time);
        }

        $result = date("Y-m", $time) . "-" . $day;

        return $result;
    }

    public function plus(string $dateFrom, string $dateTo, $type="time") : string
    {
        if ($type == "time"){
            $time = strtotime("2020-01-01 " . $dateTo) - strtotime("2020-01-01 00:00:00");

            return date("H:i:s", strtotime($dateFrom) + $time);
        }

        return "";
    }

}

