<?php

namespace App\Libs\Common;

use App\Models\Notices\NoticeCommunicationMaster;
use App\Models\Notices\NoticeCommunicationUser;
use App\Models\Notices\NoticeCommunicationTeacher;
use App\Models\Notices\NoticeMail;
use App\Models\Teachers\Teacher;
use App\Models\Users\User;

class MailTemplateClass
{
    private ?User $user = null;
    private array $userEmails = [];
    private ?Teacher $teacher = null;
    private string $master = "contact@tejima.jp";
    private array $variables = [];


    public function setUserId(int $id)
    {
        $this->user = User::find($id);
    }

    public function setUserEmails(array $userEmails=[])
    {
        $this->userEmails = $userEmails;
    }
    /**
     * 講師情報の設定
     * @param int $id
     */
    public function setTeacherId(int $id)
    {
        $this->teacher = Teacher::find($id);
    }

    /**
     * データ保持
     * @param  array  $data
     */
    public function setVariable(array $data)
    {
        $this->variables = array_merge($this->variables,$data);
    }

    /**
     * メール送付
     * @param  int  $id
     * @param  array  $mailContents
     */
    public function send(int $id, array $mailContents=[])
    {
        $NoticeMail = NoticeMail::find($id);

        $ViewClass = new ViewClass();


        //コミュニケーションがある場合
        $communicationTypes = ["user", "teacher", "master"];
        $communicationModels = ["user" => new NoticeCommunicationUser(), "teacher" => new NoticeCommunicationTeacher(), "master" => new NoticeCommunicationMaster()];

        $variables = $this->variables;
        $variables["domain"] = env("APP_URL");

        //変数の合成
        if ($this->user){
            $variables = array_merge($this->user->toArray(), $variables);
        }
        if ($this->teacher){
            $variables = array_merge($this->teacher->toArray(), $variables);
        }

        //登録予定があるものを登録
        foreach ($communicationTypes as $communicationType){
            $keyBody = $communicationType . "_communication";

            if (
                (!empty($NoticeMail->$keyBody)) ||
                (!empty($mailContents[$keyBody]))
            ){
                $Model = new $communicationModels[$communicationType];
                $Model->notice_mail_id = $id;

                if ($this->user) {
                    $Model->user_id = $this->user->id;
                }


                if ($this->teacher){
                    $Model->teacher_id = $this->teacher->id;
                }



                if (
                    (!empty($NoticeMail->$keyBody)) ||
                    (!empty($mailContents[$keyBody]))
                ){

                    $body = "";
                    if (!empty($mailContents[$keyBody])){
                        $body = $mailContents[$keyBody];
                    }else{
                        $body = $ViewClass->render($NoticeMail->$keyBody, $variables);
                    }

                    $Model->contents = $body;
                    $Model->save();
                }
            }
        }

        if ((!empty($NoticeMail->line_body)) || (!empty($mailContents["line_body"]))){
            //LINEがある場合
            if ($this->user->line){
                if (!empty($mailContents["line_body"])){
                    $body = $mailContents["line_body"];
                }else{
                    $body = $ViewClass->render($this->user->line, $variables);
                }
                $LineClass = new LineClass();
                $LineClass ->sendMessage($this->user->line, $body);
            }
        }

        $toMails = [];

        //メールの送信
        $mailTypes = ["user", "teacher", "master"];

        //予定があるものを登録
        foreach ($mailTypes as $mailType){
            $keySubject = $mailType . "_subject";
            $keyBody = $mailType . "_body";

            if ((!empty($NoticeMail->$keySubject)) || (!empty($mailContents[$keySubject]))){
                if ($this->$mailType){
                    if (isset($this->$mailType->email)){
                        $toMails[$mailType]["emails"][] = $this->$mailType->email;
                    }else{
                        $toMails[$mailType]["emails"][] = $this->$mailType;
                    }


                    if (!empty($mailContents[$keySubject])){
                        $toMails[$mailType]["subject"] = $mailContents[$keySubject];
                        $toMails[$mailType]["body"] = $mailContents[$keyBody];
                    }else{
                        $toMails[$mailType]["subject"] = $NoticeMail->$keySubject;
                        $toMails[$mailType]["body"] = $NoticeMail->$keyBody;
                    }

                    if (env("APP_DEBUG")){
                        $toMails[$mailType]["subject"] = "【テスト送信】" . $toMails[$mailType]["subject"];
                    }
                }
            }
        }

        if (!empty($this->userEmails)){
            $toMails["user"]["emails"] = $this->userEmails;
        }

        foreach ($toMails as $toMail){
            //タイトルと本文の変数変換
            if ($toMail["subject"]){
                $subject = $ViewClass->render($toMail["subject"], $variables);
            }
            if ($toMail["body"]) {
                $body = $ViewClass->render($toMail["body"], $variables);
            }


            $from = "auto_mail@williesenglish.com";
            if (!empty($NoticeMail->from)){
                $from = $NoticeMail->from;
            }

            foreach ($toMail["emails"] as $email) {
                if ($email){
                    $MailClass = new MailClass();
                    $MailClass->sendGrid($email, $from, "WiLLes English", $subject, $body);
                }
            }
        }
    }

}
