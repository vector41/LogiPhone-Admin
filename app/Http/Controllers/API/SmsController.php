<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\Controller;

use App\Libs\Common\ApiClass;
use App\Libs\GroupClass;
use App\Models\LogiPhone\LPCompanyBranch;
use App\Models\LogiPhone\LPCompanyEmployee;
use App\Models\LogiPhone\LPCompanyEmployeeRole;
use App\Models\LogiScope\CompanyBranch;
use App\Models\LogiScope\CompanyEmployee;
use App\Models\LogiPhone\LPCall;
use App\Models\LogiPhone\LPSms;
use App\Models\LogiPhone\Setting;
use App\Models\LogiScope\CompanyEmployeeJobChange;
use App\Models\LogiScope\CompanyEmployeeRole;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SMSData
{
    public $id;
    public $fullName;
    public $sendCount;
    public $receiveCount;
    function __construct($id, $name, $sendCount, $receiveCount)
    {
        $this->id = $id;
        $this->fullName = $name;
        $this->sendCount = $sendCount;
        $this->receiveCount = $receiveCount;
    }
}

class SMSDetail
{
    public $name;
    public $phone_number;
    public $date;
    public $type;

    function __construct($name, $phone_number, $date, $type)
    {
        $this->name = $name;
        $this->phone_number = $phone_number;
        $this->date = $date;
        $this->type = $type;
    }
}

class SmsController extends Controller
{
    public function getAllSms(Request $request) {}



    public function searchSms(Request $request, ApiClass $apiClass)
    {
        $role = $request->role;
        $userName = $request->userName;
        $info = DB::select("SELECT id FROM company_employees WHERE CONCAT(person_name_second,' ',person_name_first) = '$userName'");
        $res = [];
        if ($role == 1) {
            $date = $request->date;
            if (count($info) > 0) {
                $id = $info[0]->id;
                $out = DB::connection(name: 'mysql_lp')->select("SELECT count(sender_id) AS amount FROM message_histories WHERE sender_id = $id AND DATE(created_at) = '$date'");
                $in = DB::connection(name: 'mysql_lp')->select("SELECT count(receiver_id) AS amount FROM message_histories WHERE receiver_id = $id AND DATE(created_at) = '$date'");

                $sendCount = 0;
                $receiveCount = 0;
                if (count($out)) $sendCount = $out[0]->amount;
                if (count($in)) $receiveCount = $in[0]->amount;
                $newSMS = new SMSData($id, $userName, $sendCount, $receiveCount);
                array_push($res, $newSMS);
            }
        } else {
            $startDate = $request->startDate;
            $endDate = $request->endDate;

            if (count($info) > 0) {
                $id = $info[0]->id;
                $out = DB::connection(name: 'mysql_lp')->select("SELECT count(sender_id) AS amount FROM message_histories WHERE sender_id = $id AND DATE(created_at) BETWEEN '$startDate' AND '$endDate'");
                $in = DB::connection(name: 'mysql_lp')->select("SELECT count(receiver_id) AS amount FROM message_histories WHERE receiver_id = $id AND DATE(created_at) BETWEEN '$startDate' AND '$endDate'");

                $sendCount = 0;
                $receiveCount = 0;
                if (count($out)) $sendCount = $out[0]->amount;
                if (count($in)) $receiveCount = $in[0]->amount;
                $newSMS = new SMSData($id, $userName, $sendCount, $receiveCount);
                array_push($res, $newSMS);
            }
        }
        return $res;
    }




    public function insertSms(Request $request, ApiClass $apiClass)
    {
        try {
            $callNumber = $request->callNumber;
            $receiveNumber = $request->receiveNumber;
            // $content = $request->content;

            $callPerson = DB::select("SELECT id FROM company_employees WHERE tel1 = '$callNumber' OR tel2 = '$callNumber' OR tel3 = '$callNumber'");
            // return $callPerson;
            $receivePerson = DB::select("SELECT id FROM company_employees WHERE tel1 = '$receiveNumber' OR tel2 = '$receiveNumber' OR tel3 = '$receiveNumber'");

            $newSms = new LPSms;
            $newSms->sender_id = $callPerson[0]->id;
            $newSms->receiver_id = $receivePerson[0]->id;
            // $newSms->content = $content;
            $newSms->save();
            if ($newSms->save() == 1)
                return $apiClass->responseOk(['res' => $newSms]);
            else return $apiClass->responseOk(['res' => 'error']);
        } catch (\Exception $e) {
            return $e;
        }
    }

    public function getSmsHistories(Request $request)
    {
        try {
            $role = $request->role;
            $auth = auth::user();
            $res = array();
            if ($role == 1) {
                $date = $request->date;
                if ($auth->isAdmin > 0) {
                    $users = CompanyEmployee::where('company_id', $auth->company_id)->get();
                    foreach ($users as $user) {
                        $out = DB::connection(name: 'mysql_lp')->select("SELECT count(sender_id) AS amount FROM message_histories WHERE sender_id = $user->id AND DATE(created_at) = '$date'");
                        $in = DB::connection(name: 'mysql_lp')->select("SELECT count(receiver_id) AS amount FROM message_histories WHERE receiver_id = $user->id AND DATE(created_at) = '$date'");

                        $sendCount = 0;
                        $receiveCount = 0;
                        if (count($out)) $sendCount = $out[0]->amount;
                        if (count($in)) $receiveCount = $in[0]->amount;
                        $newSMS = new SMSData($user->id, $user->person_name_second . ' ' . $user->person_name_first, $sendCount, $receiveCount);
                        array_push($res, $newSMS);
                    }
                }
            } else {
                $startDate = $request->startDate;
                $endDate = $request->endDate;
                $getRes = DB::connection('mysql_lp')->select("SELECT DATEDIFF('$endDate', '$startDate') as period");
                if ($getRes[0]->period >= 31)
                    return "data error";
                if ($auth->isAdmin > 0) {
                    $users = CompanyEmployee::where('company_id', $auth->company_id)->get();
                    foreach ($users as $user) {
                        $out = DB::connection(name: 'mysql_lp')->select("SELECT count(sender_id) AS amount FROM message_histories WHERE sender_id = $user->id AND DATE(created_at) BETWEEN '$startDate' AND '$endDate'");
                        $in = DB::connection(name: 'mysql_lp')->select("SELECT count(receiver_id) AS amount FROM message_histories WHERE receiver_id = $user->id AND DATE(created_at) BETWEEN '$startDate' AND '$endDate'");

                        $sendCount = 0;
                        $receiveCount = 0;
                        if (count($out)) $sendCount = $out[0]->amount;
                        if (count($in)) $receiveCount = $in[0]->amount;
                        $newSMS = new SMSData($user->id, $user->person_name_second . ' ' . $user->person_name_first, $sendCount, $receiveCount);
                        array_push($res, $newSMS);
                    }
                }
            }
            return $res;
        } catch (\Exception $e) {
        }
    }

    public function searchSmsHistories() {}

    public function getSmsDetails(Request $request, ApiClass $apiClass)
    {
        $userID = $request->userID;
        $role = $request->role;
        $res = [];
        if ($role == 1) {
            $date = $request->date;

            $out = DB::connection('mysql_lp')->select("SELECT * FROM `message_histories` WHERE sender_id = $userID  AND DATE('created_at') = '$date'");

            foreach ($out as $history) {
                $user = CompanyEmployee::where('id', $history->receiver_id)->get();
                $phoneNumber = $user->tel1 == null ? $user->tel2 : $user->tel3;
                $newDetail = new SMSDetail($user->person_name_second . ' ' . $user->person_name_first, $phoneNumber, $history->created_at, "送信");
                array_push($res, $newDetail);
            }
            $in = DB::connection('mysql_lp')->select("SELECT * FROM `message_histories` WHERE receiver_id = $userID  AND DATE('created_at') = '$date'");
            foreach ($in as $history) {
                $user = CompanyEmployee::where('id', $history->receiver_id)->get();
                $phoneNumber = $user->tel1 == null ? $user->tel2 : $user->tel3;
                $newDetail = new SMSDetail($user->person_name_second . ' ' . $user->person_name_first, $phoneNumber, $history->created_at, "受け取った");
                array_push($res, $newDetail);
            }
            return $res;
        } else {
            $startDate = $request->startDate;
            $endDate = $request->endDate;
            $res = DB::connection('mysql_lp')->select("SELECT DATEDIFF('$endDate','$startDate') AS period");
            if ($res[0]->period > 31) {
                return "date error";
            }
            $out =
                DB::connection('mysql_lp')->select("SELECT * FROM message_histories WHERE sender_id = $userID  AND DATE('created_at')  '$startDate' BETWEEN '$endDate'");
            $in = DB::connection("mysql_lp")->select("SELECT * FROM message_histories WHERE receiver_id = $userID  AND DATE('created_at')  '$startDate' BETWEEN '$endDate'");
            $res = [];
            foreach ($out as $history) {
                $user = CompanyEmployee::where('id', $history->receiver_id)->get();
                $phoneNumber = $user->tel1 == null ? $user->tel2 : $user->tel3;
                $newDetail = new SMSDetail($user->person_name_second . ' ' . $user->person_name_first, $phoneNumber, $history->created_at, "送信");
                array_push($res, $newDetail);
            }
            foreach ($in as $history) {
                $user = CompanyEmployee::where('id', $history->receiver_id)->get();
                $phoneNumber = $user->tel1 == null ? $user->tel2 : $user->tel3;
                $newDetail = new SMSDetail($user->person_name_second . ' ' . $user->person_name_first, $phoneNumber, $history->created_at, "受け取った");
                array_push($res, $newDetail);
            }
            return $res;
        }
    }
}