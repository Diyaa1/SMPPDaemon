<?php

use React\Http\Response;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class BulkSms{

    public static function send_bulk( $smpp, $requestParam  ){

        $logger = new Logger('global_logger');
        $logger->pushHandler(new StreamHandler('log/main.log', Logger::INFO));

        $sender = $requestParam['sender'];

        $keys_of_failed = array();

        if( $requestParam['targets'] && !count( $requestParam['targets'] )){
            return new Response(
                200,
                array(
                    'Content-Type' => 'application/json'
                ),
                json_encode([
                    'code' => 0,
                    'failed_ids' => []
                ])
            );
        }

        $logger->info("Bulk Send Started");

        foreach ($requestParam['targets'] as $key => $target) {

            $receiver = $target['receiver'];
            $message = $target['msg'];

            $encodedMessage = mb_convert_encoding($message,'UTF-8','UCS-2');
            $encodedMessage = iconv('utf-8', "UCS-2BE", $message);

            try{
                $senderAddress = new SmppAddress( $sender,SMPP::TON_ALPHANUMERIC );
                $receiverAddress = new SmppAddress( $receiver ,SMPP::TON_INTERNATIONAL,SMPP::NPI_E164 );

                $response = $smpp->sendSMS( $senderAddress,$receiverAddress, $encodedMessage, null, SMPP::DATA_CODING_UCS2, 0x01 );

                if( !$response ){
                    throw new Exception();
                }

                $logger->info("Bulk: Successful send request",[
                    "sender"   => $senderAddress,
                    "receiver" => $receiverAddress,
                    "msg"      => $message
                ]);
            }catch(Exception $e){
                $logger->warning("Bulk: Failed send request", [
                    "sender"   => $senderAddress,
                    "receiver" => $receiverAddress,
                    "msg"      => $message
                ]);
                $keys_of_failed[] = $key;
            }
        }

        $logger->info("Bulk Send End");

        return new Response(
            200,
            array(
                'Content-Type' => 'application/json'
            ),
            json_encode([
                'code' => 0,
                'failed_ids' => $keys_of_failed
            ])
        );

    }
}
