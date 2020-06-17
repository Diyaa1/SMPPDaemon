<?php

use React\Http\Response;

class BulkSms{

    public static function send_bulk( $smpp, $requestParam  ){

        $sender = $requestParam['sender'];

        $keys_of_failed = [];

        Helpers::wh_log('-----====  Bulk Start ====-----');
        foreach ($requestParam['targets'] as $key => $target) {
            
            $receiver = $target['receiver'];
            $message = $target['msg'];

            $encodedMessage = mb_convert_encoding($message,'UTF-8','UCS-2');
            $encodedMessage = iconv('utf-8', "UCS-2BE", $message);
            
            $senderAddress = new SmppAddress( $sender,SMPP::TON_ALPHANUMERIC );
            $receiverAddress = new SmppAddress( $receiver ,SMPP::TON_INTERNATIONAL,SMPP::NPI_E164 );
            try{
                $smpp->sendSMS( $senderAddress,$receiverAddress, $encodedMessage, null, SMPP::DATA_CODING_UCS2, 0x01 );
                Helpers::wh_log('----- Bulk In-Proggress : Send Request To Number ' . $receiver . 'Success');
            }catch(Exception $e){
                Helpers::wh_log('----- Bulk In-Proggress : Send Request To Number ' . $receiver . 'Failed');
                $keys_of_failed[] = $key;
            }
        }
        Helpers::wh_log('-----====  Bulk End ====-----');

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
