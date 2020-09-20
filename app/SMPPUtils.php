<?php

class SMPPUtils{
    public static function getSMPPConnection(){

        // Construct transport and client
        $transport = new SocketTransport(array($_ENV['SMPP_SERVER_IP']),$_ENV['SMPP_SERVER_PORT'], true, 'printDebug');
        $transport->setRecvTimeout(10000);
        $transport->setSendTimeout(10000);

        $smpp = new SmppClient($transport, 'printDebug');

        // Activate binary hex-output of server interaction
        $smpp->debug = true;
        $transport->debug = true;

        // Open the connection
        try{
            $transport->open();
            $smpp->bindTransmitter( "invest", "inv@2020" );
        }catch( Exception $e ){
            $now = date('m/d/Y h:i:s a', time());
            self::wh_log( "Failed Connection: " . $now  . PHP_EOL );
        }
        // Optional connection specific overrides
        SmppClient::$sms_null_terminate_octetstrings = false;
        // SmppClient::$csms_method = SmppClient::CSMS_PAYLOAD;
        // SmppClient::$sms_registered_delivery_flag = SMPP::REG_DELIVERY_SMSC_BOTH;


        return $smpp;
    }
}