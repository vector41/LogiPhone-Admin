<?php

namespace App\Http\Controllers\API;

use App\Events\sendMsg;
use App\Http\Controllers\API\Controller;

use App\Libs\Common\ApiClass;
use App\Libs\GroupClass;
use App\Models\LogiPhone\LPCompanyBranch;
use App\Models\LogiPhone\LPCompanyEmployee;
use App\Models\LogiPhone\LPCompanyEmployeeRole;
use App\Models\LogiScope\CompanyBranch;
use App\Models\LogiScope\CompanyEmployee;
use App\Models\LogiPhone\LPCall;
use App\Models\LogiPhone\Setting;
use App\Models\LogiScope\CompanyEmployeeJobChange;
use App\Models\LogiScope\CompanyEmployeeRole;
use Event;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use function PHPUnit\Framework\returnSelf;

class Call
{

    public $id;
    public $full_name;
    public $outgoing_call;
    public $call_time_outgoing;
    public $incoming_call;
    public $call_time_incoming;

    function __construct($id, $Name, $outCallCount, $outTotalTime, $inCallCount, $inTotalTime)
    {
        $this->id = $id;
        $this->full_name = $Name;
        $this->outgoing_call = $outCallCount;
        $this->call_time_outgoing = $outTotalTime;
        $this->incoming_call = $inCallCount;
        $this->call_time_incoming = $inTotalTime;
    }
}

class CallDetail
{
    public $call_name;
    public $receive_name;
    public $start_time;
    public $end_time;
    public $period;
    public $type;
    function __construct($callName, $receiverName, $startTime, $endTime, $period, $type)
    {
        $this->call_name = $callName;
        $this->receive_name = $receiverName;
        $this->start_time = $startTime;
        $this->end_time = $endTime;
        $this->period = $period;
        $this->type = $type;
    }
}


class CallController extends Controller
{
    public function callControl(Request $request)
    {
        $user = CompanyEmployee::where("id",  1)->first();
        event(new sendMsg("Hello World"));
    }
    public function insertCall(Request $request, ApiClass $apiClass)
    {
        $callNumber = $request->callNumber;
        $receiveNumber = $request->receiveNumber;
        // return json_encode(["call" => $callNumber, 'receive' => $receiveNumber]);
        $callPerson = DB::select("SELECT id FROM company_employees WHERE tel1 = '$callNumber' OR tel2 = '$callNumber' OR tel3 = '$callNumber'");
        // return $callPerson;
        $receivePerson = DB::select("SELECT id FROM company_employees WHERE tel1 = '$receiveNumber' OR tel2 = '$receiveNumber' OR tel3 = '$receiveNumber'");

        $newCall = new LPCall;
        $newCall->sender_id = $callPerson[0]->id;
        $newCall->receiver_id = $receivePerson[0]->id;
        if ($newCall->save() == 1)
            return $apiClass->responseOk(["res" => $newCall]);
    }

    public function endCall(Request $request, ApiClass $apiClass)
    {
        $callID = $request->callID;
        $receiveID = $request->receiveID;
        $date = $request->date;
        $updating_at = Date::now();
        try {
            $res = DB::connection('mysql_lp')->select("UPDATE call_histories SET role = 1,  updated_at = '$updating_at' WHERE sender_id = '$callID' AND receiver_id = '$receiveID' AND created_at = '$date' AND role = 0");
            return $apiClass->responseOk(["res" => $res]);
        } catch (\Exception $e) {
        }
    }


    public function searchCall(Request $request)
    {
        $role = $request->role;
        $userName = $request->userName;
        $info = DB::select("SELECT id FROM company_employees WHERE CONCAT(person_name_second,' ',person_name_first) = '$userName'");
        $res = [];
        if ($role == 1) {
            $date = $request->date;
            if (count($info) > 0) {
                $id = $info[0]->id;
                $out = DB::connection('mysql_lp')->select("SELECT count(sender_id) AS amount, SUM(TIMEDIFF(updated_at, created_at)) / 60 AS totalTime  FROM call_histories WHERE sender_id = $id AND role = 1 AND DATE(created_at) = '$date'");
                $in = DB::connection('mysql_lp')->select("SELECT count(sender_id) AS amount, SUM(TIMEDIFF(updated_at, created_at)) / 60 AS totalTime  FROM call_histories WHERE receiver_id = $id AND role = 1 AND DATE(created_at) = '$date'");
                if (count($out) >= 1 || count($in) >= 1) {
                    $newCall = new Call($id, $userName, $out[0]->amount, $out[0]->totalTime, $in[0]->amount, $in[0]->totalTime);
                    array_push($res, $newCall);
                }
            }
        } else {
            $startDate = $request->startDate;
            $endDate = $request->endDate;
            if (count($info) > 0) $id = $info[0]->id;
            $out = DB::connection('mysql_lp')->select("SELECT count(sender_id) AS amount, SUM(TIMEDIFF(updated_at, created_at)) / 60 AS totalTime  FROM call_histories WHERE sender_id = $id AND role = 1 AND DATE(created_at) BETWEEN '$startDate' AND '$endDate'");
            $in = DB::connection('mysql_lp')->select("SELECT count(sender_id) AS amount, SUM(TIMEDIFF(updated_at, created_at)) / 60 AS totalTime  FROM call_histories WHERE receiver_id = $id AND role = 1 AND DATE(created_at) BETWEEN '$startDate' AND '$endDate'");
            if (count($out) >= 1 || count($in) >= 1) {
                $newCall = new Call($id, $userName, $out[0]->amount, $out[0]->totalTime, $in[0]->amount, $in[0]->totalTime);
                array_push($res, $newCall);
            }
        }
        return $res;
    }


    public function getCallHistories(Request $request, ApiClass $apiClass)
    {
        $auth = Auth::user();
        $userID = $auth->id;
        $unitCount = Setting::where('auth_id', $userID)->first() ? Setting::where('auth_id', $userID)->first()->unit_count : 50;
        $users = CompanyEmployee::where('company_id', $auth->company_id)->get();
        $companyUsers = CompanyEmployee::where('company_id', $auth->company_id)->get();
        $companyInUsers = $companyUsers->map(function ($item) {
            return $item->id;
        });
        // $dataBuilder = CompanyEmployee::whereIn('updated_id', $companyInUsers);
        // $users = $dataBuilder->orderBy('id', 'asc')->paginate($unitCount);
        $role = $request->role;
        $res = array();
        try {
            if ($role == 1) {
                $date = $request->date;
                foreach ($users as $user) {
                    $out = DB::connection('mysql_lp')->select("SELECT count(sender_id) AS amount, SUM(TIMEDIFF(updated_at, created_at)) / 60 AS totalTime  FROM call_histories WHERE sender_id = $user->id AND role = 1 AND DATE(created_at) = '$date'");
                    $in = DB::connection('mysql_lp')->select("SELECT count(sender_id) AS amount, SUM(TIMEDIFF(updated_at, created_at)) / 60 AS totalTime  FROM call_histories WHERE receiver_id = $user->id AND role = 1 AND DATE(created_at) = '$date'");
                    if (count($out) >= 1 || count($in) >= 1) {
                        $newCall = new Call($user->id, $user->person_name_second . ' ' . $user->person_name_first, $out[0]->amount, $out[0]->totalTime, $in[0]->amount, $in[0]->totalTime);
                        array_push($res, $newCall);
                    }
                }
            } else {
                $startDate = $request->startDate;
                $endDate = $request->endDate;
                return "SELECT DATEDIFF('$endDate', '$startDate') AS diff";
                $dateDiff = DB::connection('mysql_lp')->select("SELECT DATEDIFF('$endDate', '$startDate') AS diff");
                if ($dateDiff[0]->diff > 31)
                    return "date error";
                foreach ($users as $user) {
                    $out = DB::connection('mysql_lp')->select("SELECT count(sender_id) AS amount, SUM(TIMEDIFF(updated_at, created_at)) / 60 AS totalTime  FROM call_histories WHERE sender_id = $user->id AND role = 1 AND DATE(created_at) BETWEEN '$startDate' AND '$endDate'");
                    $in = DB::connection('mysql_lp')->select("SELECT count(sender_id) AS amount, SUM(TIMEDIFF(updated_at, created_at)) / 60 AS totalTime  FROM call_histories WHERE receiver_id = $user->id AND role = 1 AND DATE(created_at) BETWEEN '$startDate' AND '$endDate'");
                    if (count($out) >= 1 || count($in) >= 1) {
                        $newCall = new Call($user->id, $user->person_name_second . ' ' . $user->person_name_first, $out[0]->amount, $out[0]->totalTime, $in[0]->amount, $in[0]->totalTime);
                        array_push($res, $newCall);
                    }
                }
            }
            return $res;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }


    public function searchCallHistories(Request $request, ApiClass $apiClass)
    {
        $role = $request->role;
        $user = $request->user;
        if ($role == 1) {
            try {
                $date = $request->date;
                $outGoing = DB::connection("mysql_lp")->select("SELECT count(sender_id) as amount, SUM(TIMEDIFF(updated_at, created_at)) FROM call_histories WHERE sender_id = $user AND role = 1 AND DATE('created_at') = '$date'");
                $inComing = DB::connection("mysql_lp")->select("SELECT count(sender_id) as amount, SUM(TIMEDIFF(updated_at, created_at)) FROM call_histories WHERE receiver_id = $user AND role = 1 AND DATE('created_at') = '$date'");
                return $apiClass->responseOk(["out" => $outGoing, "in" => $inComing]);
                // new Event(sendMsg("Hello world"));
            } catch (\Exception $e) {
            }
        } else {
            $startDate = $request->startDate;
            $endDate = $request->endDate;
            try {
                $outGoing = DB::connection("mysql_lp")->select("SELECT count(sender_id) as amount, SUM(TIMEDIFF(updated_at, created_at)) FROM call_histories WHERE sender_id = $user AND role = 1 AND DATE('created_at') BETWEEN '$startDate' AND '$endDate'");
                $inComing = DB::connection("mysql_lp")->select("SELECT count(sender_id) as amount, SUM(TIMEDIFF(updated_at, created_at)) FROM call_histories WHERE receiver_id = $user AND role = 1 AND DATE('created_at') BETWEEN '$startDate' AND  $endDate");
                return $apiClass->responseOk(["out" => $outGoing, "in" => $inComing]);
            } catch (Exception $e) {
            }
        }
    }

    public function getCallDetailsOfDay(Request $request, ApiClass $apiClass)
    {
        $userID = $request->userID;
        $date = $request->date;

        $user = CompanyEmployee::where("id", $userID)->get();
        // $company = Company::where('id', $user->company_id)->get();
        $outGoingCalls = DB::connection('mysql_lp')->select("SELECT *, TIMEDIFF(updated_at,created_at) AS period FROM call_histories WHERE role = 1 AND sender_id = $userID AND DATE(created_at) = '$date' ORDER BY created_at DESC");
        $inComingCalls = DB::connection('mysql_lp')->select("SELECT *, TIMEDIFF(updated_at,created_at) AS period  FROM call_histories WHERE role = 1 AND receiver_id = $userID AND DATE(created_at) = '$date' ORDER BY created_at DESC");
        $res = array();
        foreach ($outGoingCalls as $call) {
            $receiveUser = CompanyEmployee::where('id', $call->receiver_id)->get();
            $callDetail = new CallDetail(
                $user->person_name_second . ' ' . $user->person_name_first,
                $receiveUser->person_name_second . ' ' . $receiveUser->person_name_first,
                $call->created_at,
                $call->updated_at,
                $call->period,
                "発信"
            );
            array_push($res, $callDetail);
        }
        foreach ($inComingCalls as $call) {
            $callUser = CompanyEmployee::where('id', $call->sender_id)->get();
            $callDetail = new CallDetail($callUser->person_name_second . ' ' . $callUser->person_name_first, $user->person_name_second . ' ' . $user->person_name_first, $call->created_at, $call->updated_at, $call->period, "着信");
            array_push($res, $callDetail);
        }
        return $apiClass->responseOk(['response' => $res]);
    }

    public function getCallDetailsOfPeriod(Request $request, ApiClass $apiClass)
    {
        $startDate = $request->startDate;
        $endDate = $request->endDate;
        $diff = DB::connection("mysql_lp")->select("SELECT DATEDIFF('$endDate','$startDate') as period");
        if ($diff[0]->period > 31) {
            return $apiClass->responseOk(["response" => "period error"]);
        }
        $userID = $request->userID;
        $user = CompanyEmployee::where("id", $userID)->get();
        // $company = Company::where('id', $user->company_id)->get();
        $outGoingCalls = DB::connection('mysql_lp')->select("SELECT *, TIMEDIFF(updated_at,created_at) AS period FROM call_histories WHERE role = 1 AND sender_id = $userID AND DATE(created_at) BETWEEN '$startDate' AND '$endDate' ORDER BY created_at DESC");
        $inComingCalls = DB::connection('mysql_lp')->select("SELECT *, TIMEDIFF(updated_at,created_at) AS period  FROM call_histories WHERE role = 1 AND receiver_id = $userID AND DATE(created_at) BETWEEN '$startDate' AND '$endDate' ORDER BY created_at DESC");
        $res = array();
        foreach ($outGoingCalls as $call) {
            $receiveUser = CompanyEmployee::where('id', $call->receiver_id)->get();
            $callDetail = new CallDetail(
                $user->person_name_second . ' ' . $user->person_name_first,
                $receiveUser->person_name_second . ' ' . $receiveUser->person_name_first,
                $call->created_at,
                $call->updated_at,
                $call->period,
                "発信"
            );
            array_push($res, $callDetail);
        }
        foreach ($inComingCalls as $call) {
            $callUser = CompanyEmployee::where('id', $call->sender_id)->get();
            $callDetail = new CallDetail($callUser->person_name_second . ' ' . $callUser->person_name_first, $user->person_name_second . ' ' . $user->person_name_first, $call->created_at, $call->updated_at, $call->period, "着信");
            array_push($res, $callDetail);
        }
        return $apiClass->responseOk(['response' => $res]);
    }
}