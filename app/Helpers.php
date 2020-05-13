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

    public static function wh_log($log_msg)
    {
        $log_filename = "log";
        if (!file_exists($log_filename)) 
        {
            // create directory/folder uploads.
            mkdir($log_filename, 0777, true);
        }
        $log_file_data = $log_filename.'/log_' . date('d-M-Y') . '.log';
        // if you don't add `FILE_APPEND`, the file will be erased each time you add a log
        file_put_contents($log_file_data, $log_msg . "\n", FILE_APPEND);
    } 

}
