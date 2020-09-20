<?php

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

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
            $logger = new Logger('global_logger');
            $logger->pushHandler(new StreamHandler('log/main.log', Logger::INFO));
            $logger->error("connection couldn't be made with the remote smpp server ");
        }
        // Optional connection specific overrides
        SmppClient::$sms_null_terminate_octetstrings = false;
        // SmppClient::$csms_method = SmppClient::CSMS_PAYLOAD;
        // SmppClient::$sms_registered_delivery_flag = SMPP::REG_DELIVERY_SMSC_BOTH;


        return $smpp;
    }
}