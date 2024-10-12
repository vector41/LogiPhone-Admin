<?php

namespace App\Libs\Common;

use App\Models\Notices\NoticeCommunicationMaster;
use App\Models\Notices\NoticeCommunicationUser;
use App\Models\Notices\NoticeCommunicationTeacher;
use App\Models\Notices\NoticeMail;

class MailClass
{

    /**
     * メール送付
     * @param int $id
     */
    public function send(int $id)
    {

    }

    public function sendGrid($to, $from, $fromName, $subject, $body)
    {
        $email = new \SendGrid\Mail\Mail();
        $email->setFrom($from, $fromName);
        $email->setSubject($subject);

        $email->addTo($to);

        //$email->addContent("text/plain");
        $email->addContent("text/html", nl2br($body));

        $sendgrid = new \SendGrid(getenv('SENDGRID_API_KEY'));
        // Send the email

        try {
            $response = $sendgrid->send($email);

            /*
            print $response->statusCode() . "\n";
            print_r($response->headers());
            print $response->body() . "\n";
            echo "email sent!\n";*/

        } catch (Exception $e) {
            debug($e->getMessage());

            //echo 'Caught exception: '. $e->getMessage() ."\n";
        }

    }
}
