<?php

namespace App\WebSockets;

use BeyondCode\LaravelWebSockets\Apps\App;
use BeyondCode\LaravelWebSockets\Dashboard\DashboardLogger;
use BeyondCode\LaravelWebSockets\Facades\StatisticsLogger;
use BeyondCode\LaravelWebSockets\QueryParameters;
use BeyondCode\LaravelWebSockets\WebSockets\Channels\ChannelManager;
use BeyondCode\LaravelWebSockets\WebSockets\Exceptions\ConnectionsOverCapacity;
use BeyondCode\LaravelWebSockets\WebSockets\Exceptions\UnknownAppKey;
use BeyondCode\LaravelWebSockets\WebSockets\Exceptions\WebSocketException;
use BeyondCode\LaravelWebSockets\WebSockets\Messages\PusherMessageFactory;
use Exception;
use Ratchet\ConnectionInterface;
use Ratchet\RFC6455\Messaging\MessageInterface;
use Ratchet\WebSocket\MessageComponentInterface;

use Illuminate\Support\Facades\DB;
use App\Models\LogiScope\CompanyEmployee;
use App\Models\LogiPhone\LPCall;
use App\Models\LogiPhone\LPSms;
use Illuminate\Support\Facades\Log;

class AndroidWebSocketsHandler implements MessageComponentInterface
{
    /** @var \BeyondCode\LaravelWebSockets\WebSockets\Channels\ChannelManager */
    protected $channelManager;
    // protected $result;

    public function __construct(ChannelManager $channelManager)
    {
        $this->channelManager = $channelManager;
    }

    public function onOpen(ConnectionInterface $connection)
    {
        $this
            ->verifyAppKey($connection)
            ->limitConcurrentConnections($connection)
            ->generateSocketId($connection)
            ->establishConnection($connection);
    }

    public function onMessage(ConnectionInterface $connection, MessageInterface $message)
    {
        // $message = PusherMessageFactory::createForMessage($message, $connection, $this->channelManager);
        $payload = json_decode($message->getPayload());
        $event = $payload->event;

        if ($event == 'VERIFY_PHONE_LIST') {
            $data = trim($payload->data, '"[]"');
            $list = explode(', ', $data);
            $temp = array();
            
            for ($i = 0; $i < count($list); $i++) {
                $count = CompanyEmployee::where(DB::raw("REGEXP_REPLACE(tel1, '[^0-9]', '')"), '=', $list[$i])
                                        ->orWhere(DB::raw("REGEXP_REPLACE(tel2, '[^0-9]', '')"), '=', $list[$i])
                                        ->orWhere(DB::raw("REGEXP_REPLACE(tel3, '[^0-9]', '')"), '=', $list[$i])
                                        ->count();

                if($count > 0) array_push($temp, $list[$i]);
            }

            $connection->send(json_encode([
                'event' => 'VERIFIED_PHONE_LIST',
                'data'  => $temp,
            ]));
            // $data = $payload->data;
            // $temp = array();

            // for ($i = 0; $i < count($data); $i++) {
            //     $count = CompanyEmployee::where('tel1', '=', $data[$i]->number)
            //                             ->orWhere('tel2', '=', $data[$i]->number)
            //                             ->orWhere('tel3', '=', $data[$i]->number)
            //                             ->count();

            //     if($count > 0) $data[$i]->shared = 1;
            //     else $data[$i]->shared = 0;

            //     array_push($temp, $data[$i]);
            // }

            // $connection->send(json_encode([
            //     'event' => 'VERIFIED_PHONE_LIST',
            //     'data'  => $temp,
            // ]));
        }

        if ($event == 'GET_USER_DETAIL') {
            $data = $payload->data;

            $detail = CompanyEmployee::select('companies.company_name', 
                                                'company_employees.id', 
                                                'company_employees.person_name_second', 
                                                'company_employees.person_name_first', 
                                                'company_employees.tel1', 
                                                'company_employees.tel2', 
                                                'company_employees.tel3')
                                    ->leftJoin('companies', 'company_employees.company_id', '=', 'companies.id')
                                    ->where(DB::raw("REGEXP_REPLACE(tel1, '[^0-9]', '')"), '=', $data)
                                    ->orWhere(DB::raw("REGEXP_REPLACE(tel2, '[^0-9]', '')"), '=', $data)
                                    ->orWhere(DB::raw("REGEXP_REPLACE(tel3, '[^0-9]', '')"), '=', $data)
                                    ->orderBy('company_employees.updated_at', 'desc')
                                    ->limit(1)
                                    ->first();

            $connection->send(json_encode([
                'event' => 'GOT_USER_DETAIL',
                'data'  => $detail,
            ]));
        }

        if ($event == 'START_CALL_LOG') {
            $data = $payload->data;

            $sender = CompanyEmployee::where(DB::raw("REGEXP_REPLACE(tel1, '[^0-9]', '')"), '=', $data->sender)
                                    ->orWhere(DB::raw("REGEXP_REPLACE(tel2, '[^0-9]', '')"), '=', $data->sender)
                                    ->orWhere(DB::raw("REGEXP_REPLACE(tel3, '[^0-9]', '')"), '=', $data->sender)
                                    ->first();
            $receiver = CompanyEmployee::where(DB::raw("REGEXP_REPLACE(tel1, '[^0-9]', '')"), '=', $data->receiver)
                                    ->orWhere(DB::raw("REGEXP_REPLACE(tel2, '[^0-9]', '')"), '=', $data->receiver)
                                    ->orWhere(DB::raw("REGEXP_REPLACE(tel3, '[^0-9]', '')"), '=', $data->receiver)
                                    ->first();

            $lp_call = new LPCall();
            $lp_call->sender_id   = $sender->id;
            $lp_call->receiver_id = $data->receiver == 0 ? 0 : $receiver->id;
            $lp_call->role        = 0;
            $lp_call->save();

            $connection->send(json_encode([
                'event' => 'STARTED_CALL_LOG',
                'data'  => true,
            ]));
        }

        if ($event == 'SAVE_CALL_LOG') {
            $data = $payload->data;

            $sender = CompanyEmployee::where(DB::raw("REGEXP_REPLACE(tel1, '[^0-9]', '')"), '=', $data->sender)
                                    ->orWhere(DB::raw("REGEXP_REPLACE(tel2, '[^0-9]', '')"), '=', $data->sender)
                                    ->orWhere(DB::raw("REGEXP_REPLACE(tel3, '[^0-9]', '')"), '=', $data->sender)
                                    ->first();
            $receiver = CompanyEmployee::where(DB::raw("REGEXP_REPLACE(tel1, '[^0-9]', '')"), '=', $data->receiver)
                                    ->orWhere(DB::raw("REGEXP_REPLACE(tel2, '[^0-9]', '')"), '=', $data->receiver)
                                    ->orWhere(DB::raw("REGEXP_REPLACE(tel3, '[^0-9]', '')"), '=', $data->receiver)
                                    ->first();

            $lp_call = LPCall::where('sender_id', '=', $sender->id)
                            ->where('receiver_id', '=', $receiver == null ? 0 : $receiver->id)
                            ->where('role', '=', 0)
                            ->first();
            if ($lp_call != null) {
                $lp_call->role = 1;
                $lp_call->save();
            }

            $connection->send(json_encode([
                'event' => 'SAVED_CALL_LOG',
                'data'  => true,
            ]));
        }

        if ($event == 'SAVE_SMS_LOG') {
            $data = $payload->data;

            $sender = CompanyEmployee::where(DB::raw("REGEXP_REPLACE(tel1, '[^0-9]', '')"), '=', $data->sender)
                                    ->orWhere(DB::raw("REGEXP_REPLACE(tel2, '[^0-9]', '')"), '=', $data->sender)
                                    ->orWhere(DB::raw("REGEXP_REPLACE(tel3, '[^0-9]', '')"), '=', $data->sender)
                                    ->first();
            $receiver = CompanyEmployee::where(DB::raw("REGEXP_REPLACE(tel1, '[^0-9]', '')"), '=', $data->receiver)
                                    ->orWhere(DB::raw("REGEXP_REPLACE(tel2, '[^0-9]', '')"), '=', $data->receiver)
                                    ->orWhere(DB::raw("REGEXP_REPLACE(tel3, '[^0-9]', '')"), '=', $data->receiver)
                                    ->first();

            $lp_sms = new LPSms();
            $lp_sms->sender_id   = $sender->id;
            $lp_sms->receiver_id = $receiver == null ? 0 : $receiver->id;
            $lp_sms->content     = $data->content;
            $lp_sms->save();

            $connection->send(json_encode([
                'event' => 'SAVED_SMS_LOG',
                'data'  => true,
            ]));
        }

        // StatisticsLogger::webSocketMessage($connection);
    }

    public function onClose(ConnectionInterface $connection)
    {
        $this->channelManager->removeFromAllChannels($connection);

        DashboardLogger::disconnection($connection);

        StatisticsLogger::disconnection($connection);
    }

    public function onError(ConnectionInterface $connection, Exception $exception)
    {
        if ($exception instanceof WebSocketException) {
            $connection->send(json_encode(
                $exception->getPayload()
            ));
        }
    }

    protected function verifyAppKey(ConnectionInterface $connection)
    {
        $appKey = QueryParameters::create($connection->httpRequest)->get('appKey');

        if (! $app = App::findByKey($appKey)) {
            throw new UnknownAppKey($appKey);
        }

        $connection->app = $app;

        return $this;
    }

    protected function limitConcurrentConnections(ConnectionInterface $connection)
    {
        if (! is_null($capacity = $connection->app->capacity)) {
            $connectionsCount = $this->channelManager->getConnectionCount($connection->app->id);
            if ($connectionsCount >= $capacity) {
                throw new ConnectionsOverCapacity();
            }
        }

        return $this;
    }

    protected function generateSocketId(ConnectionInterface $connection)
    {
        $socketId = sprintf('%d.%d', random_int(1, 1000000000), random_int(1, 1000000000));

        $connection->socketId = $socketId;

        return $this;
    }

    protected function establishConnection(ConnectionInterface $connection)
    {
        // $connection->send(json_encode([
        //     'event' => 'pusher:connection_established',
        //     'data' => json_encode([
        //         'socket_id' => $connection->socketId,
        //         'activity_timeout' => 30,
        //     ]),
        // ]));

        DashboardLogger::connection($connection);

        StatisticsLogger::connection($connection);

        return $this;
    }
}
