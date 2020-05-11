<?php

class Helpers{
    
    public static function getSMPPConnection(){
        
        // Construct transport and client
        $transport = new SocketTransport(array('10.18.0.46'),2775, true, 'printDebug');
        $transport->setRecvTimeout(10000);
        $transport->setSendTimeout(10000);
        $smpp = new SmppClient($transport);

        // Activate binary hex-output of server interaction
        $smpp->debug = true;
        $transport->debug = true;

        // Open the connection
        $transport->open();
        $smpp->bindTransmitter( "invest", "inv@2020" );

        // Optional connection specific overrides
        SmppClient::$sms_null_terminate_octetstrings = false;
        // SmppClient::$csms_method = SmppClient::CSMS_PAYLOAD;
        // SmppClient::$sms_registered_delivery_flag = SMPP::REG_DELIVERY_SMSC_BOTH;


        return $smpp;
    }

}
