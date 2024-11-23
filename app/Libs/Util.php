<?php
namespace App\Libs;

use Auth;
use App\Models\Attendance\Attend;
use App\Models\Attendance\AttendKind;
use App\Models\Attendance\AttendDaily;
use App\Models\Attendance\LsCompanies;
use App\Models\Attendance\LsEmployees;
use App\Models\Attendance\LsCompanyHolidays;
use App\Models\Attendance\LsCompanyBranches;
use App\Models\Attendance\AttendSetting;
use App\Models\Attendance\AttendTransHoliday;
use App\Models\Attendance\AttendWorkHoliday;
use App\Models\Attendance\AttendCompanySetting;
use App\Models\Attendance\PaidHolidayAmount;
use App\Models\Attendance\AttendPermission;
use App\Models\Attendance\PaidHoliday;
use App\Models\Attendance\PaidHolidayInfo;
use App\Models\Attendance\PaidHolidayAddLog;
use App\Models\Map\MapDisplayOrder;

class Util
{
    public static function getLSUserInfo()
    {
        $lsUserInfo = array();
        $lsUserInfo['id'] = null;
        $me = Auth::user();
        $lsUserInfo['id'] = $me->id;
        if ($lsUserInfo['id']) {
            $lsUser = LsEmployees::where('id', $lsUserInfo['id'])
                ->with('lsAuth')
                ->with('lsCalendar')
                ->first();
            $lsUserInfo['company'] = $lsUser->company_id;
            $lsUserInfo['lsUser'] = $lsUser;
        }
        return $lsUserInfo;
    }

    public static function getAttendCompanySetting($company)
    {
        $attendCompanySetting = AttendCompanySetting::where('company', $company)->first();
        if (!$attendCompanySetting) { // なければデフォルトで作成
            $attendCompanySetting = new AttendCompanySetting();
            $attendCompanySetting->company = $company;
            $attendCompanySetting->month_start_type = 1; // 月初
            $attendCompanySetting->paid_holiday_add_timing = 1; // 期首
            $attendCompanySetting->paid_holiday_add_type = 1; // 8割未満も付与する
            $attendCompanySetting->paid_holiday_add_month = 1; // 有給一斉付与月
            $attendCompanySetting->paid_holiday_add_duration = 6; // 入社後付与までの月数
            $attendCompanySetting->paid_holiday_add_duration_type = 1; // 入社後付与の種類
            $attendCompanySetting->paid_holiday_valid_years = 2; // 有給有効年数
            $attendCompanySetting->save();

            $daysArray = [5, 4, 3, 2, 1];
            $yearDaysMinArray = [217, 169, 121, 73, 48];
            $yearDaysMaxArray = [999, 216, 168, 120, 72];
            $additionalDaysArray = [
                [10, 11, 12, 14, 16, 18, 20, 20, 20, 20],
                [7, 8, 9, 10, 12, 13, 15, 15, 15, 15],
                [5, 6, 6, 8, 9, 10, 11, 11, 11, 11],
                [3, 4, 4, 5, 6, 6, 7, 7, 7, 7],
                [1, 2, 2, 2, 3, 3, 3, 3, 3, 3],
            ];

            for ($index = 0; $index < count($daysArray); $index++) {
                $paidHolidayAmount = new PaidHolidayAmount();
                $paidHolidayAmount->attend_company_setting_id = $attendCompanySetting->id;
                $paidHolidayAmount->days_a_week = $daysArray[$index];
                $paidHolidayAmount->min_days = $yearDaysMinArray[$index];
                $paidHolidayAmount->max_days = $yearDaysMaxArray[$index];
                for ($year = 0; $year <= 9; $year++) {
                    $variable = sprintf("additional_days_%d", $year);
                    $paidHolidayAmount->$variable = $additionalDaysArray[$index][$year];
                }
                $paidHolidayAmount->save();
            }
        }

        $attendCompanySetting->load('paidHolidayAmounts');

        return $attendCompanySetting;

    }
    public static function getAttendKindMap()
    {
        $attendKinds = AttendKind::all();
        $attendKindMap = [];
        foreach ($attendKinds as $attendKind) {
            $attendKindMap[$attendKind->id] = $attendKind;
        }
        return $attendKindMap;
    }
    public static function getAttendSetting($company, $lsCalendarXid)
    {
        $attendSetting = 'not found';
        $lsCalendar = LsCompanyHolidays::company($company)->where('id', $lsCalendarXid)->first();
        if ($lsCalendar) {
            $attendSetting = AttendSetting::company($company)->where('calendar_xid', $lsCalendarXid)->first();
            if (!$attendSetting) {
                /*
                {
                    "一日稼働日": {
                        "休憩終了分": [
                            "0"
                        ],
                        "休憩終了時": [
                            "13"
                        ],
                        "休憩開始分": [
                            "0"
                        ],
                        "休憩開始時": [
                            "12"
                        ],
                        "勤務終了分": "0",
                        "勤務終了時": "17",
                        "勤務開始分": "0",
                        "勤務開始時": "8"
                    },
                    "半日稼働日": {
                        "休憩終了分": [
                            "0"
                        ],
                        "休憩終了時": [
                            "0"
                        ],
                        "休憩開始分": [
                            "0"
                        ],
                        "休憩開始時": [
                            "0"
                        ],
                        "勤務終了分": "0",
                        "勤務終了時": "0",
                        "勤務開始分": "0",
                        "勤務開始時": "0"
                    }
                }
                */
                $startTime = $lsCalendar->getOneDayStartTime();
                $endTime = $lsCalendar->getOneDayEndTime();

                // set default
                $attendSetting = new AttendSetting();
                $attendSetting->company = $company;
                $attendSetting->calendar_xid = $lsCalendarXid;
                /*
                $attendSetting->day_start_time = $day_start_time;
                $attendSetting->atuo_switch = $atuo_switch;
                $attendSetting->auto_switch_time = $auto_switch_time;
                $attendSetting->total_rounding = $total_rounding;
                $attendSetting->total_rounding_type = $total_rounding_type;
                $attendSetting->total_rounding_unit = $total_rounding_unit;
                $attendSetting->start_rounding = $start_rounding;
                $attendSetting->start_rounding_type = $start_rounding_type;
                $attendSetting->start_rounding_unit = $start_rounding_unit;
                $attendSetting->end_rounding = $end_rounding;
                $attendSetting->end_rounding_type = $end_rounding_type;
                $attendSetting->end_rounding_unit = $end_rounding_unit;
                */
                $attendSetting->early_time = $startTime;
                /*
                $attendSetting->early_rounding = $early_rounding;
                $attendSetting->early_rounding_type = $early_rounding_type;
                $attendSetting->early_rounding_unit = $early_rounding_unit;
                */
                $attendSetting->late_time = $endTime;
                /*
                $attendSetting->late_rounding = $late_rounding;
                $attendSetting->late_rounding_type = $late_rounding_type;
                $attendSetting->late_rounding_unit = $late_rounding_unit;
                */
                $attendSetting->midnight_time = '22:00:00';
                /*
                $attendSetting->midnight_rounding = $midnight_rounding;
                $attendSetting->midnight_rounding_type = $midnight_rounding_type;
                $attendSetting->midnight_rounding_unit = $midnight_rounding_unit;
                */
                $attendSetting->graph_start_time = '05:00:00';
                $attendSetting->save();
                $attendSetting->refresh();
            }
        }
        return $attendSetting;
    }
    public static function getSecondsForTime($timeString)
    {
        $seconds = 0;
        if (strlen($timeString) == 5) {
            $seconds = substr($timeString, 0, 2) * 3600 + substr($timeString, 3, 2) * 60;
        } else if (strlen($timeString) == 8) {
            $seconds = substr($timeString, 0, 2) * 3600 + substr($timeString, 3, 2) * 60 + substr($timeString, 6, 2);
        }
        return $seconds;
    }
    public static function getSecondsFromTimeString($timeString)
    {
        return substr($timeString, 0, 2) * 3600 + substr($timeString, 3, 2) * 60 + substr($timeString, 6, 2);
    }
    public static function compareFiscalDate($dateTimeString, $attendSetting)
    {
        $when = 0;
        $todayStartString = date('Y-m-d ') . $attendSetting->day_start_time;
        $todayStartTime = strtotime($todayStartString);
        $todayEndTime = $todayStartTime + 86400 - 1;

        $targetTime = strtotime($dateTimeString);
        if ($targetTime < $todayStartTime) {
            // past
            $when = -1;
        } else if ($todayEndTime < $targetTime) {
            // future
            $when = 1;
        }

        return $when;
    }
    public static function getAttendDaily($company, $lsUserXid, $date, $attendSetting, $attendCompanySetting, $lsCalendar)
    {
        $workType = $lsCalendar->getWorkTypeFromDateString($date);
        $attendDaily = AttendDaily::company($company)->where('user', $lsUserXid)->where('date', $date)->first();
        if (!$attendDaily) {
            if ($workType !== null) {
                $attendDaily = new AttendDaily();
                $attendDaily->company = $company;
                $attendDaily->user = $lsUserXid;
                $attendDaily->date = $date;
            } else {
                // カレンダーがなければ作成しない（できない）
                $attendDaily = null;
            }
        }
        if ($attendDaily) {
            $dayStartTimeString = $date . ' ' . $attendSetting->day_start_time;
            $when = Util::compareFiscalDate($dayStartTimeString, $attendSetting);
            $attendInfo = Util::getAttendInfo($company, $date, $lsUserXid, $attendSetting, $lsCalendar);
            if ($when < 0) {
                // 過去のデータを更新
                if (($attendCompanySetting->no_punch_behavior == 1) || $attendInfo->isAttend) {
                    if ($attendDaily->attend_kind_id == 0) {
                        // 勤務種別がまだ設定されていなければ該当日の Attendデータを探してきて設定する
                        $attendDaily->attend_kind_id = $attendInfo->attendKind;
                    }
                    if ($attendDaily->start === null) {
                        $attendDaily->start = $attendInfo->start;
                        $attendDaily->start_attend_id = $attendInfo->startAttendId;
                    }
                    if ($attendDaily->end === null) {
                        $attendDaily->end = $attendInfo->end;
                        $attendDaily->end_attend_id = $attendInfo->endAttendId;
                    }
                } else {
                    // 打刻がない場合は欠勤扱い
                    if ($attendDaily->attend_kind_id == 0) {
                        // 勤務種別がまだ設定されていなければ該当日の Attendデータを探してきて設定する
                        $attendDaily->attend_kind_id = 6; // 欠勤
                    }
                }
                $attendDaily->should_fix = 0;
                if ($attendInfo->errorCount > 0) {
                    // 出退勤の打刻のどちらかがない
                    if (
                        ($attendDaily->start_attend_id || $attendDaily->start_changed) &&
                        ($attendDaily->end_attend_id || $attendDaily->end_changed)
                    ) {
                        // 打刻か修正が両方にあればOK
                        $attendDaily->should_fix = 0;
                        $attendDaily->error_type = 0;
                    } else {
                        $attendDaily->should_fix = 1;
                        if ($attendDaily->start_attend_id || $attendDaily->start_changed) {
                            $attendDaily->error_type = 2; // 退勤打刻無し
                        } else {
                            $attendDaily->error_type = 1; // 出勤打刻無し
                        }
                    }
                }

                if (strtotime($attendDaily->start) > strtotime($attendDaily->end)) {
                    // 出勤時間よりも退勤時間のほうが早ければ修正が必要
                    $attendDaily->should_fix = 1;
                    $attendDaily->error_type = 3; // 不正時刻
                }
            } else if ($when == 0) {
                // 本日のデータ
                if ($attendInfo->isAttend) {
                    // 打刻データがあれば記録
                    if ($attendDaily->attend_kind_id == 0) {
                        // 勤務種別がまだ設定されていなければ該当日の Attendデータを探してきて設定する
                        $attendDaily->attend_kind_id = $attendInfo->attendKind;
                    }
                    if (($attendDaily->start === null) && ($attendInfo->startExists)) {
                        $attendDaily->start = $attendInfo->start;
                        $attendDaily->start_attend_id = $attendInfo->startAttendId;
                    }
                    if (($attendDaily->end === null) && ($attendInfo->endExists)) {
                        $attendDaily->end = $attendInfo->end;
                        $attendDaily->end_attend_id = $attendInfo->endAttendId;
                    }
                    $attendDaily->should_fix = 0;
                }
            } else {
                // 未来のデータ
                // 未来の場合は何もセットしない
            }

            $attendDaily->save();
        }

        return $attendDaily;

    }
    public static function getDayOfTheWeek($time)
    {
        $week = [
            '日',
            //0
            '月',
            //1
            '火',
            //2
            '水',
            //3
            '木',
            //4
            '金',
            //5
            '土',
            //6
        ];

        $date = date('w', $time);
        return $week[$date];
    }
    public static function getHourMinString($seconds)
    {
        $hour = floor($seconds / 3600);
        $min = floor($seconds / 60) % 60;
        return sprintf("%02d:%02d", $hour, $min);
    }
    public static function shouldCountWorkTimeFor($attendKindId)
    {
        $shouldCount = true;
        // switch ($attendKindId) {
        //     case 1: // 出勤', 'カレンダーで【一日出勤】【半日出勤】【ＦＥＬＥＸ①】【ＦＥＬＥＸ②】の場合は出勤', '2021-03-22 02:34:01', '2021-03-22 02:34:01'),
        //     case 4: // 午前有休', '', '2021-03-22 02:37:51', '2021-03-22 02:37:51'),
        //     case 5: // 午後有休', '', '2021-03-22 02:37:51', '2021-03-22 02:37:51'),
        //     case 7: // 振出', '振替出勤　→　欠勤と相殺する', '2021-03-22 02:37:51', '2021-03-22 02:37:51'),
        //     case 9: // 休出', '休日出勤', '2021-03-22 02:37:51', '2021-03-22 02:37:51'),
        //     case 12: // 直行直帰', '欠勤ではなく、出勤日数にカウントする', '2021-03-22 02:37:51', '2021-03-22 02:37:51'),
        //     case 13: // 直行', '出勤ボタンは押していないが、朝から出勤したのと同じ扱い。', '2021-03-22 02:37:51', '2021-03-22 02:37:51'),
        //     case 14: // 直帰', '直帰ボタンは押していないが、会社で退勤ボタンを押したのと同じ扱い。', '2021-03-22 02:37:51', '2021-03-22 02:37:51');
        //         $shouldCount = true;
        //         break;
        // }
        return $shouldCount;
        /*
        1,出勤
        2,公休
        3,一日有休
        4,半日有休
        5,一日欠勤
        6,半日欠勤　　　　　　　
        7,振替出勤
        8,振替休日
        9,一日出勤
        10,半日出勤
        11,一日代休
        12,半日代休　　　　　　
        13,特別休暇
        14,一日出張
        15,半日出張
        16,直行直帰
        17直行
        (1, '出勤', 'カレンダーで【一日出勤】【半日出勤】【ＦＥＬＥＸ①】【ＦＥＬＥＸ②】の場合は出勤', '2021-03-22 02:34:01', '2021-03-22 02:34:01'),
        (2, '公休', 'カレンダーの休日が公休', '2021-03-22 02:37:51', '2021-03-22 02:37:51'),
        (3, '有休', '有給休暇　（手入力で切り替える）', '2021-03-22 02:37:51', '2021-03-22 02:37:51'),
        (4, '午前有休', '', '2021-03-22 02:37:51', '2021-03-22 02:37:51'),
        (5, '午後有休', '', '2021-03-22 02:37:51', '2021-03-22 02:37:51'),
        (6, '欠勤', '', '2021-03-22 02:37:51', '2021-03-22 02:37:51'),
        (7, '振出', '振替出勤　→　欠勤と相殺する', '2021-03-22 02:37:51', '2021-03-22 02:37:51'),
        (8, '振休', '振替休日　→　休日出勤と相殺する', '2021-03-22 02:37:51', '2021-03-22 02:37:51'),
        (9, '休出', '休日出勤', '2021-03-22 02:37:51', '2021-03-22 02:37:51'),
        (10, '特休', '忌引きなどの特別休暇', '2021-03-22 02:37:51', '2021-03-22 02:37:51'),
        (11, '出張', '出張なので、出勤日数にカウントする', '2021-03-22 02:37:51', '2021-03-22 02:37:51'),
        (12, '直行直帰', '欠勤ではなく、出勤日数にカウントする', '2021-03-22 02:37:51', '2021-03-22 02:37:51'),
        (13, '直行', '出勤ボタンは押していないが、朝から出勤したのと同じ扱い。', '2021-03-22 02:37:51', '2021-03-22 02:37:51'),
        (14, '直帰', '直帰ボタンは押していないが、会社で退勤ボタンを押したのと同じ扱い。', '2021-03-22 02:37:51', '2021-03-22 02:37:51');
        (15, '代休');
        */


    }
    public static function getAttendInfo($company, $date, $lsUserXid, $attendSetting, $lsCalendar)
    {
        $workType = $lsCalendar->getWorkTypeFromDateString($date);
        $offset = Util::getSecondsForTime($attendSetting->day_start_time);
        $attends = Util::getAttends($company, $date, $offset, $lsUserXid);
        $start = '';
        $startAttendId = 0;
        $end = '';
        $endAttendId = 0;
        $isAttend = false;
        $startExists = false;
        $endExists = false;
        foreach ($attends as $attend) {
            $isAttend = true;
            if ($attend->action == 1) {
                // 出勤
                $start = $attend->action_time;
                $startAttendId = $attend->xid;
                $startExists = true;
            } else if ($attend->action == 2) {
                // 退勤
                $end = $attend->action_time;
                $endAttendId = $attend->xid;
                $endExists = true;
            }
        }

        if ($startExists == $endExists) {
            $errorCount = 0;
        } else {
            $errorCount = 1;
        }

        if ($workType == 0) {
            if (!$start) {
                $start = $date . ' ' . $lsCalendar->getOneDayStartTime();
            }
            if (!$end) {
                $end = $date . ' ' . $lsCalendar->getOneDayEndTime();
            }
        } else if ($workType == 1) {
            if (!$start) {
                $start = $date . ' ' . $lsCalendar->getHalfDayStartTime();
            }
            if (!$end) {
                $end = $date . ' ' . $lsCalendar->getHalfDayEndTime();
            }
        } else {
            if (!$start) {
                $start = null;
            }
            if (!$end) {
                $end = null;
            }
        }

        if ($isAttend) {
            if ($workType == 2) { // 公休
                $attendKind = 9; // 休出
            } else {
                $attendKind = 1; // 出勤
            }
        } else {
            if ($workType == 2) { // 公休
                $attendKind = 2; // 公休
            } else {
                $attendKind = 1; // 出勤
            }
        }

        $attendInfo = new \stdClass();
        $attendInfo->start = $start;
        $attendInfo->startAttendId = $startAttendId;
        $attendInfo->end = $end;
        $attendInfo->endAttendId = $endAttendId;
        $attendInfo->isAttend = $isAttend;
        $attendInfo->startExists = $startExists;
        $attendInfo->endExists = $endExists;
        $attendInfo->errorCount = $errorCount;
        $attendInfo->attendKind = $attendKind;

        return $attendInfo;

    }
    public static function getAttends($company, $date, $offset, $lsUserXid)
    {
        $dateStartTime = strtotime($date) + $offset;
        $startDateTime = date('Y-m-d H:i:s', $dateStartTime);
        $endDateTime = date('Y-m-d H:i:s', $dateStartTime + 86400);
        $attends = Attend::company($company)->where('user', $lsUserXid)->where('action_time', '>=', $startDateTime)->where('action_time', '<', $endDateTime)->get();
        return $attends;
    }
    public static function getAttendMonthly($company, $lsUserXid, $lsUserDriver, $yearMonth, $attendSetting, $lsCalendar)
    {
        $attendMonthly = new \stdClass;
        $attendMonthly->list = [];

        $showDefault = $attendSetting->show_default;

        $totalHolidayWorkDuration = 0;
        $totalEarlyDuration = 0;
        $totalnormalDuration = 0;
        $totalLateDuration = 0;
        $totalMidnightDuration = 0;
        $totalRestDuration = 0;
        $totalTotalWorkDuration = 0;
        $totalShouldWorkDuration = 0;

        $year = substr($yearMonth, 0, 4);
        $month = substr($yearMonth, 4, 2);

        $lsUser = LsEmployees::company($company)->where('id', $lsUserXid)->first();
        $attendCompanySetting = Util::getAttendCompanySetting($company);
        $monthStartType = $attendCompanySetting->month_start_type;
        $fiscalLastDayOfTheMonth = $lsUser->getFiscalLastDayOfTheMonth();
        $offsetString = $attendSetting->day_start_time;
        if (($monthStartType == 1) || ($fiscalLastDayOfTheMonth == 0)) { // 月初
            $startTimeString = sprintf("%04d-%02d-01 %s", $year, $month, $offsetString);
            $endDate = date('Y-m-01', strtotime('first day of next month', strtotime($startTimeString)));
            $endTimeString = sprintf("%s %s", $endDate, $offsetString);
        } else if ($monthStartType == 2) { // 給与締日
            $firstDayOfThisMonth = sprintf("%04d-%02d-01", $year, $month);
            $startTimeString = date('Y-m-d', strtotime('first day of last month', strtotime($firstDayOfThisMonth)) + 86400 * $fiscalLastDayOfTheMonth); // 前月頭 + 締日＊24*3600
            $endDate = date('Y-m-d', strtotime($firstDayOfThisMonth) + 86400 * $fiscalLastDayOfTheMonth - 1);
            $endTimeString = sprintf("%s %s", $endDate, $offsetString);
        }


        //$startTimeString = sprintf("%04d-%02d-01 %s",$year,$month,$attendSetting->day_start_time);
        $startTime = strtotime($startTimeString);
        //$endTimeString = date('Y-m-01 '.$attendSetting->day_start_time, strtotime('first day of next month',strtotime($startTimeString)));
        $endTime = strtotime($endTimeString);

        $attendMonthly->startTimeString = $startTimeString;
        $attendMonthly->endTimeString = $endTimeString;

        $attends = Attend::company($company)
            ->where('user', $lsUserXid)
            ->where('action_time', '>=', $startTimeString)
            ->where('action_time', '<', $endTimeString)
            ->get();

        $attendKindMap = Util::getAttendKindMap();
        $daysShouldWork = 0;
        $cumulativeWorkDuration = 0;

        $daysWork = 0;
        $daysPaidHoliday = 0;
        $daysEarlyHalfPaidHoliday = 0;
        $daysLateHalfPaidHoliday = 0;
        $daysAbsent = 0;
        $daysWorkForTransHoliday = 0;
        $daysHolidayForTransWork = 0;
        $daysHolidayWork = 0;
        $daysHolidayForHolidayWork = 0;
        $daysSpecialHoliday = 0;
        $daysBeLate = 0;
        $daysLeaveEarly = 0;

        for ($targetTime = $startTime; $targetTime < $endTime; $targetTime += 86400) {
            $yearMonthDay = sprintf("%s/%s/%s", date('Y', $targetTime), date('m', $targetTime), date('d', $targetTime));
            $attendDaily = Util::getAttendDaily($company, $lsUserXid, $yearMonthDay, $attendSetting, $attendCompanySetting, $lsCalendar);
            if (!isset($attendDaily)) {
                continue;
            }
            $dailyData = new \stdClass;
            $dailyData->date = date('Ymd', $targetTime);

            $dayOfTheWeek = Util::getDayOfTheWeek($targetTime);
            $day = date('j', $targetTime);
            $dateString = date(sprintf('d %s', $dayOfTheWeek), $targetTime);
            $workType = $lsCalendar->getWorkType(date('Y', $targetTime), date('n', $targetTime), date('d', $targetTime));
            if ($workType !== null) {
                switch ($workType) {
                    case 0:
                        $dateType = 'normal';
                        $expectedAction = '１日稼働日';
                        $daysShouldWork += 1.0;
                        break;
                    case 1:
                        $dateType = 'saturday';
                        $expectedAction = '半日稼働日';
                        $daysShouldWork += 0.5;
                        break;
                    case 2:
                        $dateType = 'holiday';
                        $expectedAction = '法定休日';
                        break;
                    case 3:
                        $dateType = 'holiday';
                        $expectedAction = '法定外休日';
                        break;
                    default:
                        $dateType = 'holiday';
                        $expectedAction = '未設定';
                        break;
                }
            } else {
                $dateType = 'holiday';
                $expectedAction = '未設定';
            }

            if (isset($attendDaily->attend_kind_id) && isset($attendKindMap[$attendDaily->attend_kind_id]) && $attendDaily->attend_kind_id) {
                $takenAction = $attendKindMap[$attendDaily->attend_kind_id]->name;
                $takenActionColor = $attendDaily->attend_kind_changed ? "#ff0000" : "#000000";
            } else {
                $takenAction = "　";
            }

            // 出勤時刻の設定
            $foundAttend = Util::findAttend($attends, 1, date('Y-m-d', $targetTime), $attendSetting);
            if ($foundAttend == null) {
                $startTimePunch = '　';
                $startImage = '';
            } else {
                $startTimePunch = substr($foundAttend->action_time, 11, 5);
                $startImage = $foundAttend->picture;
            }

            if ($attendDaily->start) {
                $startFixTime = substr($attendDaily->start, 11, 5);
            } else {
                $startFixTime = '';
            }
            $startFixColor = $attendDaily->start_changed ? "#ff0000" : "#000000";


            // 退勤時刻の設定
            $foundAttend = Util::findAttend($attends, 2, date('Y-m-d', $targetTime), $attendSetting);
            if ($foundAttend == null) {
                $endTimePunch = '　';
                $endImage = '';
            } else {
                $endTimePunch = substr($foundAttend->action_time, 11, 5);
                $endImage = $foundAttend->picture;
            }

            if ($attendDaily->end) {
                $endFixTime = substr($attendDaily->end, 11, 5);
            } else {
                $endFixTime = '';
            }
            $endFixColor = $attendDaily->end_changed ? "#ff0000" : "#000000";

            // 休憩時間の設定
            //$restInSec = Util::getTotalRestDuration($attendDaily,$lsCalendar,$attendSetting);

            $durations = Util::getDailyDurations($attendDaily, $lsCalendar, $attendSetting);

            $totalWorkDuration = $durations->holidayWorkDuration + $durations->earlyDuration + $durations->normalDuration + $durations->lateDuration + $durations->midnightDuration;
            $cumulativeWorkDuration += $totalWorkDuration;

            $shouldWorkDuration = Util::getHourMinString($durations->shouldWorkDuration);
            $holidayWorkTime = Util::getHourMinString($durations->holidayWorkDuration);
            $earlyTime = Util::getHourMinString($durations->earlyDuration);
            $normalTime = Util::getHourMinString($durations->normalDuration);
            $lateTime = Util::getHourMinString($durations->lateDuration);
            $midnightTime = Util::getHourMinString($durations->midnightDuration);
            $restTime = Util::getHourMinString($durations->totalRestDuration);
            $totalWorkTime = Util::getHourMinString($totalWorkDuration);
            $cumulativeWorkTime = Util::getHourMinString($cumulativeWorkDuration);

            $totalHolidayWorkDuration += $durations->holidayWorkDuration;
            $totalEarlyDuration += $durations->earlyDuration;
            $totalnormalDuration += $durations->normalDuration;
            $totalLateDuration += $durations->lateDuration;
            $totalMidnightDuration += $durations->midnightDuration;
            $totalRestDuration += $durations->totalRestDuration;
            $totalTotalWorkDuration += $totalWorkDuration;
            $totalShouldWorkDuration += $durations->shouldWorkDuration;

            $beLate = false;
            $leaveEarly = false;

            switch ($attendDaily->attend_kind_id) {
                case 1: //	出勤	カレンダーで【一日出勤】【半日出勤】【ＦＥＬＥＸ①】【ＦＥＬＥＸ②】の場合は出勤
                    if ($workType == 1) {
                        $daysWork += 0.5;
                        if ($durations->beLateDuration > 0) {
                            //$daysBeLate += 0.5;
                            $daysBeLate += 1;
                            $beLate = true;
                        }
                        if ($durations->leaveEarlyDuration > 0) {
                            //$daysLeaveEarly += 0.5;
                            $daysLeaveEarly += 1;
                            $leaveEarly = true;
                        }
                    } else {
                        $daysWork++;
                        if ($durations->beLateDuration > 0) {
                            $daysBeLate++;
                            $beLate = true;
                        }
                        if ($durations->leaveEarlyDuration > 0) {
                            $daysLeaveEarly++;
                            $leaveEarly = true;
                        }
                    }
                    break;
                case 2: //	公休	カレンダーの休日が公休
                    break;
                case 3: //	有休	有給休暇　（手入力で切り替える）
                    $daysPaidHoliday++;
                    break;
                case 4: //	午前有休
                    $daysEarlyHalfPaidHoliday += 0.5;
                    if ($durations->leaveEarlyDuration > 0) {
                        $daysLeaveEarly++;
                        $leaveEarly = true;
                    }
                    break;
                case 5: //	午後有休
                    $daysLateHalfPaidHoliday += 0.5;
                    if ($durations->beLateDuration > 0) {
                        $daysBeLate++;
                        $beLate = true;
                    }
                    break;
                case 6: //	欠勤
                    $daysAbsent++;
                    break;
                case 7: //	振出	振替出勤　→　振替休日と相殺する
                    //$daysAbsent--;
                    $daysWorkForTransHoliday++;
                    break;
                case 8: //	振休	振替休日　→　休日出勤と相殺する
                    //$daysHolidayWork--;
                    $daysHolidayForTransWork++;
                    break;
                case 9: //	休出	休日出勤
                    $daysHolidayWork++;
                    break;
                case 10: //	特休	忌引きなどの特別休暇
                    $daysSpecialHoliday++;
                    break;
                case 11: //	出張	出張なので、出勤日数にカウントする
                    $daysWork++;
                    break;
                case 12: //	直行直帰	欠勤ではなく、出勤日数にカウントする
                    $daysWork++;
                    break;
                case 13: //	直行	出勤ボタンは押していないが、朝から出勤したのと同じ扱い。
                    $daysWork++;
                    if ($durations->leaveEarlyDuration > 0) {
                        $daysLeaveEarly++;
                        $leaveEarly = true;
                    }
                    break;
                case 14: //	直帰	直帰ボタンは押していないが、会社で退勤ボタンを押したのと同じ扱い。
                    $daysWork++;
                    if ($durations->beLateDuration > 0) {
                        $daysBeLate++;
                        $beLate = true;
                    }
                    break;
                case 15: // 代休
                    $daysHolidayForHolidayWork++;
                    break;
            }



            $dailyData->dateType = $dateType;
            $dailyData->day = $day;
            $dailyData->dayOfTheWeek = $dayOfTheWeek;
            $dailyData->dateString = $dateString;
            $dailyData->expectedAction = $expectedAction;
            $dailyData->takenAction = $takenAction;
            $dailyData->takenActionColor = $takenActionColor;
            $dailyData->startTime = $startTimePunch;
            $dailyData->startFixTime = $startFixTime;
            $dailyData->startFixColor = $startFixColor;
            $dailyData->startImage = $startImage;
            $dailyData->endTime = $endTimePunch;
            $dailyData->endFixTime = $endFixTime;
            $dailyData->endFixColor = $endFixColor;
            $dailyData->endImage = $endImage;

            $dailyData->shouldWorkDuration = $shouldWorkDuration;
            $dailyData->holidayWorkTime = $holidayWorkTime;
            $dailyData->earlyTime = $earlyTime;
            $dailyData->normalTime = $normalTime;
            $dailyData->lateTime = $lateTime;
            $dailyData->midnightTime = $midnightTime;
            $dailyData->restTime = $restTime;
            $dailyData->totalWorkTime = $totalWorkTime;
            $dailyData->cumulativeWorkTime = $cumulativeWorkTime;
            $dailyData->errorCount = $attendDaily->should_fix;
            $dailyData->errorType = $attendDaily->error_type;
            $dailyData->note = $attendDaily->note;
            $dailyData->attendKindId = $attendDaily->attend_kind_id;
            $dailyData->beLate = $beLate;
            $dailyData->leaveEarly = $leaveEarly;
            /*
            $dailyData->totalRestTime = Util::getHourMinString($totalRestTime);
            $dailyData->showData = Util::shouldCountWorkTimeFor($attendDaily->attend_kind_id);
            */
            if (Util::shouldCountWorkTimeFor($attendDaily->attend_kind_id)) {
                if (
                    !$showDefault &&
                    !$attendDaily->start_attend_id &&
                    !$attendDaily->start_changed &&
                    !$attendDaily->end_attend_id &&
                    !$attendDaily->end_changed
                ) {
                    $dailyData->showData = false;
                } else {
                    $dailyData->showData = true;
                }
            } else {
                $dailyData->showData = false;
            }


            $attendMonthly->list[] = $dailyData;

            //　総計を加算
        }


        $attendMonthly->holidayWorkTime = Util::getHourMinString($totalHolidayWorkDuration);
        $attendMonthly->earlyTime = Util::getHourMinString($totalEarlyDuration);
        $attendMonthly->normalTime = Util::getHourMinString($totalnormalDuration);
        $attendMonthly->lateTime = Util::getHourMinString($totalLateDuration);
        $attendMonthly->midnightTime = Util::getHourMinString($totalMidnightDuration);
        $attendMonthly->restTime = Util::getHourMinString($totalRestDuration);
        $attendMonthly->totalWorkTime = Util::getHourMinString($totalTotalWorkDuration);
        if (isset($cumulativeWorkTime)) {
            $attendMonthly->cumulativeWorkTime = $cumulativeWorkTime;
        }
        $attendMonthly->totalShouldWorkDuration = Util::getHourMinString($totalShouldWorkDuration);


        /* 日別
        出勤区分予定
        出勤区分実際
        出勤時刻
        出勤打刻
        退勤時刻
        退勤打刻
        休憩時間
        労働時間
        早朝残業
        残業時間
        深夜残業
        休日労働
        総労働時間
        累計労働時間（総労働時間の累積）
        在籍時間
        備考
        */

        /* 出勤状況 */
        $attendMonthly->daysShouldWork = $daysShouldWork; //所定日数
        $attendMonthly->daysWork = $daysWork; // 出勤日数	999.0日
        $attendMonthly->daysHolidayWork = $daysHolidayWork; // 休日出勤日数	99.0日
        $attendMonthly->daysHolidayForHolidayWork = $daysHolidayForHolidayWork; // 代休日数	99.0日
        $attendMonthly->daysWorkForTransHoliday = $daysWorkForTransHoliday; // 振替出勤日数	99.0日
        $attendMonthly->daysHolidayForTransWork = $daysHolidayForTransWork; // 振替休日日数	99.0日
        $attendMonthly->daysPaidHoliday = $daysPaidHoliday; // 有給日数	99.0日
        $attendMonthly->daysEarlyHalfPaidHoliday = $daysEarlyHalfPaidHoliday; // 午前有給	99.0日
        $attendMonthly->daysLateHalfPaidHoliday = $daysLateHalfPaidHoliday; // 午後有給	99.0日
        $attendMonthly->daysSpecialHoliday = $daysSpecialHoliday; // 特休日数	99.0日
        $attendMonthly->daysAbsent = $daysAbsent; // 欠勤日数	99.0日
        $attendMonthly->daysBeLate = $daysBeLate; // 遅刻日数	99.0日
        $attendMonthly->daysLeaveEarly = $daysLeaveEarly; // 早退日数	99.0日

        /*　総計　*/
        //*/




        /* 勤務時間
        所定時間	9999:33
        労働時間	9999:33
        早朝残業	999:33
        残業時間	999:33
        深夜残業	999:33
        休日労働時間	9999:33
        休憩時間超過	9999:33
        総労働時間	9999:33
        */

        return $attendMonthly;
    }
    public static function findAttend($attends, $action, $date, $attendSetting)
    {
        $foundAttend = null;
        $startTimeString = $date . ' ' . $attendSetting->day_start_time;
        $startTime = strtotime($startTimeString);
        $endTime = $startTime + 86400;
        foreach ($attends as $attend) {
            if ($attend->action == $action) {
                $actionTime = strtotime($attend->action_time);
                if (($startTime <= $actionTime) && ($actionTime < $endTime)) {
                    $foundAttend = $attend;
                    break;
                }
            }
        }
        return $foundAttend;
    }
    public static function getDailyDurations($attendDaily, $lsCalendar, $attendSetting)
    {
        $timeOffset = Util::getSecondsForTime($attendSetting->day_start_time);

        $workType = $lsCalendar->getWorkTypeFromDateString($attendDaily->date);

        $year = substr($attendDaily->date, 0, 4);
        $month = substr($attendDaily->date, 5, 2);
        $day = substr($attendDaily->date, 8, 2);

        $dateStartTime = strtotime($attendDaily->date);
        $attendStartTime = strtotime($attendDaily->start) - $dateStartTime; // 出勤時刻
        $attendEndTime = strtotime($attendDaily->end) - $dateStartTime; // 退勤時刻
        $earlyStartTime = Util::getSecondsForTime($attendSetting->day_start_time); // 早朝残業開始時刻（労働日開始時刻）
        $normalStartTime = Util::getSecondsForTime($attendSetting->early_time); // 通常労働開始時刻（早朝残業終了時刻）
        $lateStartTime = Util::getSecondsForTime($attendSetting->late_time); // 残業開始時刻
        $midnightStartTime = Util::getSecondsForTime($attendSetting->midnight_time); // 深夜残業開始時刻
        $endTime = strtotime($attendSetting->day_start_time) + 86400; // 深夜残業終了時刻（労働日終了時刻）

        // 遅刻早退チェック
        $shouldStartBefore = 0;
        $shouldEndAfter = 0;
        if ($workType == 0) {
            $shouldStartBefore = Util::getSecondsForTime($lsCalendar->getOneDayStartTime());
            $shouldEndAfter = Util::getSecondsForTime($lsCalendar->getOneDayEndTime());
        } else if ($workType == 1) {
            $shouldStartBefore = Util::getSecondsForTime($lsCalendar->getHalfDayStartTime());
            $shouldEndAfter = Util::getSecondsForTime($lsCalendar->getHalfDayEndTime());
        }

        $beLateDuration = 0;
        if ($shouldStartBefore > 0) {
            if ($shouldStartBefore < $attendStartTime) {
                // 遅刻
                $beLateDuration = $attendStartTime - $shouldStartBefore;
            }
        }

        $leaveEarlyDuration = 0;
        if ($shouldEndAfter > 0) {
            if ($attendEndTime < $shouldEndAfter) {
                // 早退
                $leaveEarlyDuration = $shouldEndAfter - $attendEndTime;
            }
        }

        // 休憩時間の一覧を取得
        $rests = $lsCalendar->getRests($year, $month, $day);

        // 総休憩時間を取得
        $totalRestDuration = Util::getRestDuration($attendStartTime, $attendEndTime, $rests, $timeOffset);

        // 所定労働時間
        if (($shouldStartBefore > 0) && ($shouldEndAfter > 0)) {
            $shouldWorkDuration = Util::getWorkDuration($shouldStartBefore, $shouldEndAfter, $shouldStartBefore, $shouldEndAfter, $rests, $timeOffset);
        } else {
            $shouldWorkDuration = 0;
        }


        // 各種時間計算
        if ($workType == 2) {
            // 休日
            $holidayWorkDuration = Util::getWorkDuration($attendStartTime, $attendEndTime, $attendStartTime, $attendEndTime, $rests, $timeOffset);
            $earlyDuration = 0;
            $normalDuration = 0;
            $lateDuration = 0;
            $midnightDuration = 0;
        } else {
            $holidayWorkDuration = 0;
            $earlyDuration = Util::getWorkDuration($earlyStartTime, $normalStartTime, $attendStartTime, $attendEndTime, $rests, $timeOffset);
            $normalDuration = Util::getWorkDuration($normalStartTime, $lateStartTime, $attendStartTime, $attendEndTime, $rests, $timeOffset);
            $lateDuration = Util::getWorkDuration($lateStartTime, $midnightStartTime, $attendStartTime, $attendEndTime, $rests, $timeOffset);
            $midnightDuration = Util::getWorkDuration($midnightStartTime, $endTime, $attendStartTime, $attendEndTime, $rests, $timeOffset);
        }

        $dailyInfo = new \stdClass();
        /*
        $dailyInfo->attendSetting = $attendSetting;
        $dailyInfo->rests = $rests;
        $dailyInfo->workType = $workType;
        $dailyInfo->attendStart = $attendDaily->start;
        $dailyInfo->attendEnd = $attendDaily->end;
        $dailyInfo->dateStartTime = $dateStartTime;
        $dailyInfo->attendStartTime = $attendStartTime;
        $dailyInfo->attendEndTime = $attendEndTime;
        $dailyInfo->earlyStartTime = $earlyStartTime;
        $dailyInfo->normalStartTime = $normalStartTime;
        $dailyInfo->lateStartTime = $lateStartTime;
        $dailyInfo->midnightStartTime = $midnightStartTime;
        $dailyInfo->endTime = $endTime;
        //*/
        $dailyInfo->holidayWorkDuration = $holidayWorkDuration;
        $dailyInfo->earlyDuration = $earlyDuration;
        $dailyInfo->normalDuration = $normalDuration;
        $dailyInfo->lateDuration = $lateDuration;
        $dailyInfo->midnightDuration = $midnightDuration;
        $dailyInfo->totalRestDuration = $totalRestDuration;
        $dailyInfo->beLateDuration = $beLateDuration;
        $dailyInfo->leaveEarlyDuration = $leaveEarlyDuration;
        $dailyInfo->shouldWorkDuration = $shouldWorkDuration;

        return $dailyInfo;
    }
    public static function getWorkDuration($startTime, $endTime, $attendStartTime, $attendEndTime, $rests, $timeOffset)
    {
        // 早朝残業時間計算
        if ($startTime < $attendStartTime) {
            $startTime = $attendStartTime;
        }
        if ($attendEndTime < $endTime) {
            $endTime = $attendEndTime;
        }
        $workDuration = $endTime - $startTime;
        if ($workDuration > 0) {
            // 労働時間が含まれていれば休憩時間を計算して差し引く
            $restDuration = Util::getRestDuration($startTime, $endTime, $rests, $timeOffset);
            if ($restDuration > 0) {
                $workDuration -= $restDuration;
            }
        } else {
            $workDuration = 0;
        }
        return $workDuration;
    }
    public static function getRestDuration($startTimeInSec, $endTimeInSec, $rests, $timeOffset)
    {
        $totalRestTime = 0;
        foreach ($rests as $rest) {
            $restStartTimeInSec = Util::getSecondsForTime($rest->start);
            if ($restStartTimeInSec < $timeOffset) {
                $restStartTimeInSec += 86400;
            }
            $restEndTimeInSec = Util::getSecondsForTime($rest->end);
            if ($restEndTimeInSec < $timeOffset) {
                $restEndTimeInSec += 86400;
            }

            // 休憩時間の開始後に出勤したら調整
            if ($restStartTimeInSec < $startTimeInSec) {
                $restStartTimeInSec = $startTimeInSec;
            }

            //　休憩時間終了前に退勤したら調整
            if ($endTimeInSec < $restEndTimeInSec) {
                $restEndTimeInSec = $endTimeInSec;
            }

            $restTime = $restEndTimeInSec - $restStartTimeInSec;
            if ($restTime > 0) {
                $totalRestTime += $restTime;
            }
        }

        return $totalRestTime;
    }
    public static function getAttendStats($company, $lsUserXid, $lsUserDriver, $targetYearMonth, $attendSetting, $lsCalendar)
    {
        $attendStats = new \stdClass();
        $attendStats->daysShouldWork = 0; //所定日数
        $attendStats->daysWork = 0; // 出勤日数	999.0日
        $attendStats->daysHolidayWork = 0; // 休日出勤日数	99.0日
        $attendStats->daysHolidayForHolidayWork = 0; // 代休日数	99.0日
        $attendStats->daysWorkForTransHoliday = 0; // 振替出勤日数	99.0日
        $attendStats->daysHolidayForTransWork = 0; // 振替休日日数	99.0日
        $attendStats->daysPaidHoliday = 0; // 有給日数	99.0日
        $attendStats->daysEarlyHalfPaidHoliday = 0; // 午前有給	99.0日
        $attendStats->daysLateHalfPaidHoliday = 0; // 午後有給	99.0日
        $attendStats->daysSpecialHoliday = 0; // 特休日数	99.0日
        $attendStats->daysAbsent = 0; // 欠勤日数	99.0日
        $attendStats->daysBeLate = 0; // 遅刻日数	99.0日
        $attendStats->daysLeaveEarly = 0; // 早退日数	99.0日

        $attendStats->lastPaidHoliday = 0; // 有休残数
        $attendStats->lastTransHoliday = 0; // 振休残数
        $attendStats->lastWorkHoliday = 0; // 代休残数


        // スタート日を取得
        $lsUser = LsEmployees::Company($company)->where('id', $lsUserXid)->first();
        if ($lsUser) {
            $lsOffice = LsCompanyBranches::Company($company)->where('id', $lsUser->company_branch_id)->first();
            if ($lsOffice) {
                $lsCompany = LsCompanies::where('id', $lsUser->company_id)->first();
                if ($lsCompany) {
                    $fiscalStartDay = $lsCompany->getFiscalFirstDay($targetYearMonth);
                    $fiscalStartTime = strtotime($fiscalStartDay);
                    $attendStats->startDay = $fiscalStartDay;

                    $targetYear = substr($targetYearMonth, 0, 4);
                    $targetMonth = substr($targetYearMonth, 4, 2);
                    $endTime = strtotime('first day of next month', strtotime(sprintf('%04d-%02d-01', $targetYear, $targetMonth)));

                    $attendStats->endDay = date('Y-m-d', $endTime - 86400);
                    $year = date('Y', $fiscalStartTime);

                    $attendCompanySetting = Util::getAttendCompanySetting($lsUser->company_id);
                    $paidHolidayInfo = Util::getPaidHolidayInfo($lsUser, $attendCompanySetting);
                    $lastPaidHoliday = $paidHolidayInfo->amount;

                    $consumedTransHoliday = Util::getConsumedTransHoliday($lsUser->company_id, $lsUser, $year);
                    $transHoliday = Util::getTransHoliday($lsUser->company_id, $lsUser, $year);
                    $lastTransHoliday = $transHoliday->amount;

                    $consumedWorkHoliday = Util::getConsumedWorkHoliday($lsUser->company_id, $lsUser, $year);
                    $workHoliday = Util::getWorkHoliday($lsUser->company_id, $lsUser, $year);
                    $lastWorkHoliday = $workHoliday->amount;

                    // スタート日からターゲット月末までループ
                    $targetTime = $fiscalStartTime;
                    while ($targetTime < $endTime) {
                        $yearMonth = date('Ym', $targetTime);
                        $yearHyphenMonth = date('Y-m', $targetTime);
                        $attendMonthly = Util::getAttendMonthly($company, $lsUserXid, $lsUserDriver, $yearMonth, $attendSetting, $lsCalendar);

                        $attendStats->daysShouldWork += $attendMonthly->daysShouldWork; //所定日数
                        $attendStats->daysWork += $attendMonthly->daysWork; // 出勤日数	999.0日
                        $attendStats->daysHolidayWork += $attendMonthly->daysHolidayWork; // 休日出勤日数	99.0日
                        $attendStats->daysHolidayForHolidayWork += $attendMonthly->daysHolidayForHolidayWork; // 休日出勤日数	99.0日
                        $attendStats->daysWorkForTransHoliday += $attendMonthly->daysWorkForTransHoliday; // 振替出勤日数	99.0日
                        $attendStats->daysHolidayForTransWork += $attendMonthly->daysHolidayForTransWork; // 振替休日日数	99.0日
                        $attendStats->daysPaidHoliday += $attendMonthly->daysPaidHoliday; // 有給日数	99.0日
                        $attendStats->daysEarlyHalfPaidHoliday += $attendMonthly->daysEarlyHalfPaidHoliday; // 午前有給	99.0日
                        $attendStats->daysLateHalfPaidHoliday += $attendMonthly->daysLateHalfPaidHoliday; // 午後有給	99.0日
                        $attendStats->daysSpecialHoliday += $attendMonthly->daysSpecialHoliday; // 特休日数	99.0日
                        $attendStats->daysAbsent += $attendMonthly->daysAbsent; // 欠勤日数	99.0日
                        $attendStats->daysBeLate += $attendMonthly->daysBeLate; // 遅刻日数	99.0日
                        $attendStats->daysLeaveEarly += $attendMonthly->daysLeaveEarly; // 早退日数	99.0日

                        $targetTime = strtotime('first day of next month', $targetTime);

                        //$lastPaidHoliday -= $consumedPaidHoliday[$yearHyphenMonth];
                        $lastTransHoliday += $consumedTransHoliday[$yearHyphenMonth]->increment - $consumedTransHoliday[$yearHyphenMonth]->decrement;
                        $lastWorkHoliday += $consumedWorkHoliday[$yearHyphenMonth]->increment - $consumedWorkHoliday[$yearHyphenMonth]->decrement;
                    }

                    $attendStats->lastPaidHoliday = $lastPaidHoliday;
                    $attendStats->lastTransHoliday = $lastTransHoliday;
                    $attendStats->lastWorkHoliday = $lastWorkHoliday;

                    $ratioData = Util::getAttendRatio($company, $lsUser, $fiscalStartDay, date('Y-m-d', $endTime));
                    $attendStats->workRatio = $ratioData->ratio;
                }
            }
        }
        return $attendStats;
    }
    public static function getAttendRatio($company, $lsUser, $startDate, $endDate)
    {


        $data = new \stdClass();
        $ratio = 1.0;
        $employDate = $lsUser->employday;
        $employTime = strtotime($employDate);

        $startTime = strtotime($startDate);
        $endTime = strtotime($endDate);

        $lsUser->load('lsCalendar');

        $lsCalendar = $lsUser->lsCalendar;
        $shouldWorkCount = 0;
        $workCount = 0;

        if ($startTime < $employTime) {
            $startTime = $employTime;
            $startDate = $employDate;
        }

        $attendDailies = AttendDaily::company($lsUser->company_id)
            ->where('user', $lsUser->id)
            ->where('date', '>=', $startDate)
            ->where('date', '<', $endDate)
            ->get();

        $attendDailyMap = [];
        foreach ($attendDailies as $attendDaily) {
            $attendDailyMap[$attendDaily->date] = $attendDaily;
        }

        for ($targetTime = $startTime; $targetTime < $endTime; $targetTime += 86400) {
            $workType = $lsCalendar->getWorkType(date('Y', $targetTime), date('n', $targetTime), date('d', $targetTime));
            $targetDate = date('Y-m-d', $targetTime);

            if ($workType !== null) {
                switch ($workType) {
                    case 0:
                    case 1:
                        $shouldWorkCount += 1.0;

                        // 出勤日に記録がなければ出勤でカウント
                        if (!isset($attendDailyMap[$targetDate])) {
                            $workCount += 1.0;
                        }
                        break;
                    default:
                        break;
                }
            }

            if (isset($attendDailyMap[$targetDate])) {
                $attendDaily = $attendDailyMap[$targetDate];

                switch ($attendDaily->attend_kind_id) {
                    case 1: //	出勤	カレンダーで【一日出勤】【半日出勤】【ＦＥＬＥＸ①】【ＦＥＬＥＸ②】の場合は出勤
                    case 4: //	午前有休
                    case 5: //	午後有休
                    case 7: //	振出	振替出勤　→　振替休日と相殺する
                    case 9: //	休出	休日出勤
                    case 11: //	出張	出張なので、出勤日数にカウントする
                    case 12: //	直行直帰	欠勤ではなく、出勤日数にカウントする
                    case 13: //	直行	出勤ボタンは押していないが、朝から出勤したのと同じ扱い。
                    case 14: //	直帰	直帰ボタンは押していないが、会社で退勤ボタンを押したのと同じ扱い。
                        $workCount += 1.0;
                        break;
                    case 2: //	公休	カレンダーの休日が公休
                    case 3: //	有休	有給休暇　（手入力で切り替える）
                    case 6: //	欠勤
                    case 8: //	振休	振替休日　→　休日出勤と相殺する
                    case 10: //	特休	忌引きなどの特別休暇
                    case 15: // 代休
                        break;
                }
            }
        }


        if ($shouldWorkCount >= 1.0) {
            $ratio = $workCount / $shouldWorkCount;
        }

        $data->ratio = $ratio;
        $data->workCount = $workCount;
        $data->shouldWorkCount = $shouldWorkCount;

        return $data;
    }
    public static function getPaidHolidayInfo($lsUser, $attendCompanySetting)
    {
        $paidHolidayInfo = PaidHolidayInfo::company($lsUser->company_id)
            ->where('user', $lsUser->id)
            ->first();

        if (!$paidHolidayInfo || (strtotime($paidHolidayInfo->should_update_on) < time())) {
            $paidHolidayInfo = Util::updatePaidHolidayInfo($lsUser, $attendCompanySetting);
        }
        return $paidHolidayInfo;
    }
    public static function updatePaidHolidayInfo($lsUser, $attendCompanySetting)
    {
        // まずは直近の付与処理
        $paidHolidayAddLog = Util::addPaidHolidayIfNeeded($lsUser, $attendCompanySetting);
        $lastAddDate = $paidHolidayAddLog->added_on;

        $paidHolidays = PaidHoliday::company($lsUser->company_id)
            ->where('user', $lsUser->id)
            ->where('status', 1) // 1:有効
            ->orderBy('expired_on', 'asc') // 有効期限が早く切れるもの順
            ->orderBy('id', 'asc')
            ->get();

        foreach ($paidHolidays as $paidHoliday) {
            // 一旦使用情報をクリア
            $paidHoliday->first_used_on = null;
            $paidHoliday->first_used_amount = null;
            $paidHoliday->second_used_on = null;
            $paidHoliday->second_used_amount = null;
            $paidHoliday->attend_daily_id = 0;
        }

        $attendDailies = AttendDaily::company($lsUser->company_id)
            ->where('user', $lsUser->id)
            ->whereIn('attend_kind_id', [3, 4])
            /*
                3	一日有休
                4	半日有休
            */
            ->orderBy('date', 'asc') // 古い順
            ->get();

        $lackAmount = 0;

        // 有給取得を一つずつ記録していく
        foreach ($attendDailies as $attendDaily) {
            if ($attendDaily->attend_kind_id == 3) {
                $amount = 1.0;
            } else {
                $amount = 0.5;
            }

            // 記録できるものを探すループ
            foreach ($paidHolidays as $paidHoliday) {
                if ($paidHoliday->isUsableAt($attendDaily->date)) {
                    if ($amount == 1.0) {
                        // 一日有給を使う場合
                        if (!$paidHoliday->first_used_amount) {
                            // まだ使われていなければ一日分使う
                            $paidHoliday->first_used_on = $attendDaily->date;
                            $paidHoliday->first_used_amount = 1.0;
                            $amount = 0.0;
                            break; // 完了
                        } else if (($paidHoliday->first_used_amount == 0.5) && !$paidHoliday->second_used_amount) {
                            // 半日だけ使われていれば半日分使う
                            $paidHoliday->second_used_on = $attendDaily->date;
                            $paidHoliday->second_used_amount = 0.5;
                            $amount = 0.5; // 半分は次のループにまわす
                        }
                    } else if ($amount == 0.5) {
                        if (!$paidHoliday->first_used_amount) {
                            // まだ使われていなければ半日分使う
                            $paidHoliday->first_used_on = $attendDaily->date;
                            $paidHoliday->first_used_amount = 0.5;
                            $amount = 0.0;
                            break; // 完了
                        } else if (($paidHoliday->first_used_amount == 0.5) && !$paidHoliday->second_used_amount) {
                            // 半日だけ使われていれば半日分使う
                            $paidHoliday->second_used_on = $attendDaily->date;
                            $paidHoliday->second_used_amount = 0.5;
                            $amount = 0.0;
                            break; // 完了
                        }
                    }
                }
            }

            if ($amount > 0) {
                $lackAmount += $amount;
            }
        }

        $lastAmount = 0;
        $nextUpdateTime = strtotime('2099-01-01');
        $urrentTime = time();
        foreach ($paidHolidays as $paidHoliday) {
            // 変更を保存
            $paidHoliday->save();

            if (!$paidHoliday->isExpired()) {
                $lastAmount += $paidHoliday->lastAmount();
                $expirationTime = strtotime($paidHoliday->expired_on);
                if (($urrentTime < $expirationTime) && ($expirationTime < $nextUpdateTime)) {
                    $nextUpdateTime = $expirationTime;
                }
            }
        }
        $nextUpdateDate = date('Y-m-d', $nextUpdateTime);

        $paidHolidayInfo = PaidHolidayInfo::company($lsUser->company_id)
            ->where('user', $lsUser->id)
            ->first();

        if (!$paidHolidayInfo) {
            $paidHolidayInfo = new PaidHolidayInfo();
            $paidHolidayInfo->company = $lsUser->company_id;
            $paidHolidayInfo->user = $lsUser->id;
        }
        $paidHolidayInfo->amount = $lastAmount;
        $paidHolidayInfo->lack_amount = $lackAmount;
        $paidHolidayInfo->should_update_on = $nextUpdateDate;
        $paidHolidayInfo->last_added_on = $lastAddDate;
        $paidHolidayInfo->save();

        return $paidHolidayInfo;

    }
    public static function addPaidHolidayIfNeeded($lsUser, $attendCompanySetting)
    {
        $addDate = Util::getLastPaidHolidayAddDate($lsUser, $attendCompanySetting);

        $paidHolidayAddLog = PaidHolidayAddLog::company($lsUser->company_id)
            ->where('user', $lsUser->id)
            ->where('added_on', $addDate)
            ->where('reason', 1) // 定期
            ->first();

        if (!$paidHolidayAddLog) {
            // 追加されていないので追加処理を行う
            $paidHolidayAddLog = new PaidHolidayAddLog();
            $paidHolidayAddLog->company = $lsUser->company_id;
            $paidHolidayAddLog->user = $lsUser->id;
            $paidHolidayAddLog->added_on = $addDate;
            $paidHolidayAddLog->reason = 1; // 定期

            $previousAddDate = date('Y-m-d', strtotime('-1 year', strtotime($addDate)));

            $workDayCount = Util::getWorkDayCount($lsUser->company_id, $lsUser, $previousAddDate, $addDate);
            if ($workDayCount == 0) {
                $workDayCount = 365;
            }

            $addCount = Util::getAdditionalPaidHolidayCount(
                $attendCompanySetting,
                $workDayCount,
                $lsUser->employday,
                $addDate
            );

            $paidHolidayAddLog->count = $addCount;
            $paidHolidayAddLog->save();

            if ($addCount > 0) {
                $addTime = strtotime($addDate);
                $expiredTime = strtotime(sprintf('+%d year', $attendCompanySetting->paid_holiday_valid_years), $addTime);
                $expiredDate = date('Y-m-d', $expiredTime);
                // 有給を追加
                for ($index = 0; $index < $addCount; $index++) {
                    $paidHoliday = new PaidHoliday();
                    $paidHoliday->company = $lsUser->company_id;
                    $paidHoliday->user = $lsUser->id;
                    $paidHoliday->added_on = $addDate;
                    $paidHoliday->expired_on = $expiredDate;
                    $paidHoliday->status = 1; // 1:有効
                    $paidHoliday->reason = 1; // 1:定期
                    $paidHoliday->save();
                }
            }
        }

        return $paidHolidayAddLog;
    }
    public static function getAdditionalPaidHolidayCountForEmploymentLength($attendCompanySetting, $workDayCount, $employmentLength)
    {
        // 継続勤務年数（年） 0.5 1.5 2.5 3.5 4.5 5.5 6.5以上
        // 付与日数（日） 10 11 12 14 16 18 20
        $additionalPaidHolidayCount = 0;

        $paidHolidayAmount = null;
        foreach ($attendCompanySetting->paidHolidayAmounts as $workPaidHolidayAmount) {
            if (($workPaidHolidayAmount->min_days <= $workDayCount) && ($workDayCount <= $workPaidHolidayAmount->max_days)) {
                $paidHolidayAmount = $workPaidHolidayAmount;
                break;
            }
        }

        if ($paidHolidayAmount) {
            if ($employmentLength->year == 0) {
                if ($employmentLength->month < 6) {
                    $additionalPaidHolidayCount = 0;
                } else {
                    $additionalPaidHolidayCount = $paidHolidayAmount->additional_days_0;
                }
            } else if ($employmentLength->year == 1) {
                if ($employmentLength->month < 6) {
                    $additionalPaidHolidayCount = $paidHolidayAmount->additional_days_0;
                } else {
                    $additionalPaidHolidayCount = $paidHolidayAmount->additional_days_1;
                }
            } else if ($employmentLength->year == 2) {
                if ($employmentLength->month < 6) {
                    $additionalPaidHolidayCount = $paidHolidayAmount->additional_days_1;
                } else {
                    $additionalPaidHolidayCount = $paidHolidayAmount->additional_days_2;
                }
            } else if ($employmentLength->year == 3) {
                if ($employmentLength->month < 6) {
                    $additionalPaidHolidayCount = $paidHolidayAmount->additional_days_2;
                } else {
                    $additionalPaidHolidayCount = $paidHolidayAmount->additional_days_3;
                }
            } else if ($employmentLength->year == 4) {
                if ($employmentLength->month < 6) {
                    $additionalPaidHolidayCount = $paidHolidayAmount->additional_days_3;
                } else {
                    $additionalPaidHolidayCount = $paidHolidayAmount->additional_days_4;
                }
            } else if ($employmentLength->year == 5) {
                if ($employmentLength->month < 6) {
                    $additionalPaidHolidayCount = $paidHolidayAmount->additional_days_4;
                } else {
                    $additionalPaidHolidayCount = $paidHolidayAmount->additional_days_5;
                }
            } else if ($employmentLength->year == 6) {
                if ($employmentLength->month < 6) {
                    $additionalPaidHolidayCount = $paidHolidayAmount->additional_days_5;
                } else {
                    $additionalPaidHolidayCount = $paidHolidayAmount->additional_days_6;
                }
            } else if ($employmentLength->year == 7) {
                if ($employmentLength->month < 6) {
                    $additionalPaidHolidayCount = $paidHolidayAmount->additional_days_6;
                } else {
                    $additionalPaidHolidayCount = $paidHolidayAmount->additional_days_7;
                }
            } else if ($employmentLength->year == 8) {
                if ($employmentLength->month < 6) {
                    $additionalPaidHolidayCount = $paidHolidayAmount->additional_days_7;
                } else {
                    $additionalPaidHolidayCount = $paidHolidayAmount->additional_days_8;
                }
            } else if ($employmentLength->year == 9) {
                if ($employmentLength->month < 6) {
                    $additionalPaidHolidayCount = $paidHolidayAmount->additional_days_8;
                } else {
                    $additionalPaidHolidayCount = $paidHolidayAmount->additional_days_9;
                }
            } else if ($employmentLength->year > 10) {
                $additionalPaidHolidayCount = $paidHolidayAmount->additional_days_9;
            }
        }

        return $additionalPaidHolidayCount;
    }
    public static function getAdditionalPaidHolidayCount($attendCompanySetting, $workDayCount, $employmentDate, $targetDate)
    {
        $additionalPaidHolidayCount = 0;
        // 前日の一年後の勤続年数分を付与
        $criteriaDate = date('Y-m-d', strtotime('+1 year', strtotime($targetDate)) - 86400);
        $currentEmploymentLength = Util::getEmploymentLength($employmentDate, $targetDate);
        if (
            (($currentEmploymentLength->year == 0) && ($currentEmploymentLength->month >= 6)) ||
            ($currentEmploymentLength->year >= 1)
        ) {
            $criteriaEmploymentLength = Util::getEmploymentLength($employmentDate, $criteriaDate);
            $additionalPaidHolidayCount = Util::getAdditionalPaidHolidayCountForEmploymentLength($attendCompanySetting, $workDayCount, $criteriaEmploymentLength);
        }
        return $additionalPaidHolidayCount;
    }
    public static function getEmploymentLength($employmentDate, $targetDate)
    {
        /*
        民法で次のように規定されています。

        （暦による期間の計算）
        第百四十三条　週、月又は年によって期間を定めたときは、その期間は、暦に従って計算する。
        ２　週、月又は年の初めから期間を起算しないときは、その期間は、最後の週、月又は年においてその起算日に応当する日の前日に満了する。ただし、月又は年によって期間を定めた場合において、最後の月に応当する日がないときは、その月の末日に満了する。

        つまり、「6か月」の期間は、30日×6ではなく、暦の「月」で数え、6個先の月の応当日が期間満了日となります。
        1月15日から起算する場合、6か月の期間満了日は7月15日です。
        */

        $employmentLength = new \stdClass();

        $isLastDay = false;

        $targetYear = substr($targetDate, 0, 4);
        $targetMonth = substr($targetDate, 5, 2);
        $targetDay = substr($targetDate, 8, 2);
        $lastDayOfTargetMonth = date('d', strtotime('last day of ' . $targetDate));
        if ($targetDay == $lastDayOfTargetMonth) {
            $isLastDay = true;
        }

        $employmentYear = (int) substr($employmentDate, 0, 4);
        $employmentMonth = (int) substr($employmentDate, 5, 2);
        $employmentDay = (int) substr($employmentDate, 8, 2);

        $employmentLength->year = $targetYear - $employmentYear;
        $employmentLength->month = $targetMonth - $employmentMonth;
        if ($targetDay < $employmentDay) {
            if (!$isLastDay) {
                $employmentLength->month--;
            }
        }

        if ($employmentLength->month < 0) {
            $employmentLength->month += 12;
            $employmentLength->year--;
        }

        return $employmentLength;
    }
    public static function getWorkDayCount($company, $lsUser, $startDate, $endDate)
    {
        $startTime = strtotime($startDate);
        $endTime = strtotime($endDate);

        $lsCalendar = $lsUser->lsCalendar;
        $shouldWorkCount = 0;

        for ($targetTime = $startTime; $targetTime < $endTime; $targetTime += 86400) {
            $workType = $lsCalendar->getWorkType(date('Y', $targetTime), date('n', $targetTime), date('d', $targetTime));
            if ($workType !== null) {
                switch ($workType) {
                    case 0:
                    case 1:
                        $shouldWorkCount += 1.0;
                        break;
                    default:
                        break;
                }
            }
        }
        return $shouldWorkCount;
    }
    public static function getLastPaidHolidayAddDate($lsUser, $attendCompanySetting)
    {
        $date = Util::getPaidHolidayAddDate($lsUser, $attendCompanySetting);
        $currentYear = date('Y');
        $addDate = date(sprintf('%d-%s', $currentYear, $date)); // 今年の付与日
        if (time() <= strtotime($addDate)) {
            // 今年の付与日が未来ならば1年戻す
            $addDate = date(sprintf('%d-%s', $currentYear - 1, $date)); // 去年の付与日
        }
        return $addDate;
    }
    public static function getPaidHolidayAddDate($lsUser, $attendCompanySetting)
    {
        $addTiming = $attendCompanySetting->paid_holiday_add_timing;
        $addType = $attendCompanySetting->paid_holiday_add_type;
        $durationType = $attendCompanySetting->paid_holiday_add_duration_type;

        $addDate = '';
        if ($addTiming == AttendCompanySetting::ADD_TIMING_YEAR) {
            $addMonth = $lsUser->getFiscalStartMonth();
            $addDate = sprintf('%02d-01', $addMonth);
        } else if ($addTiming == AttendCompanySetting::ADD_TIMING_MONTH) { // 指定月に一斉付与
            $addMonth = $attendCompanySetting->paid_holiday_add_month;
            $addDate = sprintf('%02d-01', $addMonth);
        } else if ($addTiming == AttendCompanySetting::ADD_TIMING_IMMEDIATE) { // 入社月から個別付与
            $addDate = substr($lsUser->employday, 5, 5);
        } else if ($addTiming == AttendCompanySetting::ADD_TIMING_SPECIFIC) { // 入社日の指定月後の月または翌月に個別付与
            $duration = $attendCompanySetting->paid_holiday_add_duration;
            $employDate = date(sprintf('Y-%s', substr($lsUser->employday, 5, 5)));
            $addDateTime = strtotime(sprintf('+%d month', $duration), strtotime($employDate));
            $addDate = date('m-d', $addDateTime);
            // 月の最終日を超えてしまったら、前月の最終日に戻す
            if (substr($addDate, 3, 2) < substr($employDate, 8, 2)) {
                $addDateTime = strtotime('last day of previous month', $addDateTime);
                $addDate = date('m-d', $addDateTime);
            }

            if ($attendCompanySetting->paid_holiday_add_duration_type == AttendCompanySetting::DURATION_TYPE_IMMEDIATE) {
                // そのまま
            } else if ($attendCompanySetting->paid_holiday_add_duration_type == AttendCompanySetting::DURATION_TYPE_NEXT_MONTH) {
                // 次の給与締日翌日に付与
                $lastDay = $lsUser->getFiscalLastDayOfTheMonth();
                $day = date('d', $addDateTime);
                if ($day <= $lastDay) {
                    // 同じ月の締日翌日
                    $diff = $lastDay - $day;
                    $addDateTime += 86400 * ($diff + 1);
                } else {
                    // 翌月の締日翌日
                    $addDateTime = strtotime('first day of next month', $addDateTime);
                    $addDateTime += 86400 * $lastDay;
                }
                $addDate = date('m-d', $addDateTime);
            }
        }

        return $addDate;
    }
    public static function getConsumedTransHoliday($company, $lsUser, $fiscalYear)
    {
        $fiscalStartMonth = $lsUser->getFiscalStartMonth();
        $startDate = sprintf('%04d-%02d-01', $fiscalYear, $fiscalStartMonth);
        $endDate = date('Y-m-d', strtotime('+1 year', strtotime($startDate)));

        $attendDailies = AttendDaily::company($company)
            ->where('user', $lsUser->id)
            ->where('date', '>=', $startDate)
            ->where('date', '<', $endDate)
            ->whereIn('attend_kind_id', [7, 8])
            ->orderBy('date')->get();

        $consumedTransHolidays = [];
        $consumedTransHolidays['total'] = new \stdClass();
        $consumedTransHolidays['total']->increment = 0;
        $consumedTransHolidays['total']->decrement = 0;
        for ($index = 0; $index < 12; $index++) {
            $description = $startDate . ' +' . $index . ' month';
            $yearMonth = date('Y-m', strtotime($description));
            $consumedTransHolidays[$yearMonth] = new \stdClass();
            $consumedTransHolidays[$yearMonth]->increment = 0;
            $consumedTransHolidays[$yearMonth]->decrement = 0;
        }

        foreach ($attendDailies as $attendDaily) {
            $yearMonth = substr($attendDaily->date, 0, 7);
            if ($attendDaily->attend_kind_id == 8) {
                $consumedTransHolidays[$yearMonth]->decrement++;
                $consumedTransHolidays['total']->decrement++;
            } else {
                $consumedTransHolidays[$yearMonth]->increment++;
                $consumedTransHolidays['total']->increment++;
            }
        }

        return $consumedTransHolidays;
    }
    public static function getTransHoliday($company, $lsUser, $fiscalYear)
    {
        $transHoliday = AttendTransHoliday::company($company)->where('user', $lsUser->id)->where('fiscal_year', $fiscalYear)->first();
        if (!$transHoliday) {
            // なければ作成する
            $transHoliday = new AttendTransHoliday();
            $transHoliday->company = $company;
            $transHoliday->user = $lsUser->id;
            $transHoliday->fiscal_year = $fiscalYear;

            $lastTransHolidayCount = 0;
            $lastTransHoliday = AttendTransHoliday::company($company)->where('user', $lsUser->id)->where('fiscal_year', $fiscalYear - 1)->first();
            if ($lastTransHoliday) {
                // 前年のものがあればそこから引き継ぐ
                $consumedTransHolidays = Util::getConsumedTransHoliday($company, $lsUser, $fiscalYear - 1);
                $lastTransHolidayCount = $lastTransHoliday->amount + $consumedTransHolidays['total']->increment - $consumedTransHolidays['total']->decrement;
            }

            $transHoliday->last_amount = $lastTransHolidayCount;
            $transHoliday->additional_amount = 0;
            $transHoliday->amount = $transHoliday->last_amount + $transHoliday->additional_amount;

            $transHoliday->save();

        }
        return $transHoliday;
    }
    public static function getConsumedWorkHoliday($company, $lsUser, $fiscalYear)
    {
        $fiscalStartMonth = $lsUser->getFiscalStartMonth();
        $startDate = sprintf('%04d-%02d-01', $fiscalYear, $fiscalStartMonth);
        $endDate = date('Y-m-d', strtotime('+1 year', strtotime($startDate)));

        $attendDailies = AttendDaily::company($company)
            ->where('user', $lsUser->id)
            ->where('date', '>=', $startDate)
            ->where('date', '<', $endDate)
            ->whereIn('attend_kind_id', [9, 15]) // 9:休出 15:代休
            ->orderBy('date')->get();

        $consumedWorkHolidays = [];
        $consumedWorkHolidays['total'] = new \stdClass();
        $consumedWorkHolidays['total']->increment = 0;
        $consumedWorkHolidays['total']->decrement = 0;
        for ($index = 0; $index < 12; $index++) {
            $description = $startDate . ' +' . $index . ' month';
            $yearMonth = date('Y-m', strtotime($description));
            $consumedWorkHolidays[$yearMonth] = new \stdClass();
            $consumedWorkHolidays[$yearMonth]->increment = 0;
            $consumedWorkHolidays[$yearMonth]->decrement = 0;
        }

        foreach ($attendDailies as $attendDaily) {
            $yearMonth = substr($attendDaily->date, 0, 7);
            if ($attendDaily->attend_kind_id == 15) {
                $consumedWorkHolidays[$yearMonth]->decrement++;
                $consumedWorkHolidays['total']->decrement++;
            } else {
                $consumedWorkHolidays[$yearMonth]->increment++;
                $consumedWorkHolidays['total']->increment++;
            }
        }

        return $consumedWorkHolidays;
    }
    public static function getWorkHoliday($company, $lsUser, $fiscalYear)
    {
        $workHoliday = AttendWorkHoliday::company($company)->where('user', $lsUser->id)->where('fiscal_year', $fiscalYear)->first();
        if (!$workHoliday) {
            // なければ作成する
            $workHoliday = new AttendWorkHoliday();
            $workHoliday->company = $company;
            $workHoliday->user = $lsUser->id;
            $workHoliday->fiscal_year = $fiscalYear;

            $lastWorkHolidayCount = 0;
            $lastWorkHoliday = AttendWorkHoliday::company($company)->where('user', $lsUser->id)->where('fiscal_year', $fiscalYear - 1)->first();
            if ($lastWorkHoliday) {
                // 前年のものがあればそこから引き継ぐ
                $consumedWorkHolidays = Util::getConsumedWorkHoliday($company, $lsUser, $fiscalYear - 1);
                $lastWorkHolidayCount = $lastWorkHoliday->amount + $consumedWorkHolidays['total']->increment - $consumedWorkHolidays['total']->decrement;
            }

            $workHoliday->last_amount = $lastWorkHolidayCount;
            $workHoliday->additional_amount = 0;
            $workHoliday->amount = $workHoliday->last_amount + $workHoliday->additional_amount;

            $workHoliday->save();

        }
        return $workHoliday;
    }
    public static function isAttendModifyPermitted($company, $lsUserXid)
    {
        $attendPermission = AttendPermission::company($company)->where('user', $lsUserXid)->first();
        $isPermitted = false;
        if ($attendPermission && $attendPermission->modify) {
            $isPermitted = true;
        }
        return $isPermitted;
    }
    public static function getTimeOffset($company, $lsUserXid)
    {
        $timeOffset = 18000; // 一日の開始時刻　5時なら 5x3600
        $lsUser = LsEmployees::company($company)->where('id', $lsUserXid)->where('attendance_type', 1)->first();
        if ($lsUser) {
            $attendSetting = Util::getAttendSetting($company, $lsUser->company_holiday_id);
            if ($attendSetting != 'not found') {
                $startTime = $attendSetting->day_start_time;
                $timeOffset = Util::getSecondsForTime($startTime);
            }
        }
        return $timeOffset;
    }

    public static function sortMaps($userId, $maps)
    {
        $mapDisplayOrder = MapDisplayOrder::where('user_id', $userId)->first();
        if ($mapDisplayOrder) {
            $sortedMaps = [];

            $mapsMap = [];
            foreach ($maps as $map) {
                $mapsMap[$map->id] = $map;
            }

            $orders = explode(",", $mapDisplayOrder->order);
            foreach ($orders as $targetMapId) {
                if (isset($mapsMap[$targetMapId]) && ($mapsMap[$targetMapId] != null)) {
                    $sortedMaps[] = $mapsMap[$targetMapId];
                    $mapsMap[$targetMapId] = null;
                }
            }

            // オーダーに登録されていないものは最初に挿入
            foreach ($maps as $map) {
                if (isset($mapsMap[$map->id]) && ($mapsMap[$map->id] != null)) {
                    array_unshift($sortedMaps, $mapsMap[$map->id]);
                    $mapsMap[$map->id] = null;
                }
            }
        } else {
            $sortedMaps = $maps;
        }
        return $sortedMaps;
    }
}