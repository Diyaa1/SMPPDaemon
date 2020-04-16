<?php

use React\Http\Server;
use React\Http\Response;
use Psr\Http\Message\ServerRequestInterface;
require_once('sockettransport.class.php');
require_once('smppclient.class.php');
require_once('gsmencoder.class.php');

function printDebug($str) {
		echo date('Ymd H:i:s ').$str."\r\n";
}

$loop = React\EventLoop\Factory::create();

$loop->addPeriodicTimer(10, function () {
 
  // Construct transport and client
  $transport = new SocketTransport(array('213.139.63.169'),3700, true, 'printDebug');
  $transport->setRecvTimeout(10000);
  $transport->setSendTimeout(10000);
  $smpp = new SmppClient($transport);

  // Activate binary hex-output of server interaction
  $smpp->debug = true;
  $transport->debug = true;

  // Open the connection
  $transport->open();
  $smpp->bindTransmitter( "client351", "B2B@351" );

  // Optional connection specific overrides
  SmppClient::$sms_null_terminate_octetstrings = false;
  // SmppClient::$csms_method = SmppClient::CSMS_PAYLOAD;
  // SmppClient::$sms_registered_delivery_flag = SMPP::REG_DELIVERY_SMSC_BOTH;
  // Prepare message
  $message = '1234';
  $encodedMessage = mb_convert_encoding($message,'uft-8','UCS-2');

  $sender = new SmppAddress( 'gadha',SMPP::TON_ALPHANUMERIC );
  $reciver = new SmppAddress( "962796251527",SMPP::TON_INTERNATIONAL,SMPP::NPI_E164 );

  $smpp->sendSMS( $sender,$reciver,$message, null, SMPP::DATA_CODING_DEFAULT, 0x01 );
});

$loop->run();








// $server = new Server(function (ServerRequestInterface $request) use( $smpp ) {

//     $queryParams = $request->getQueryParams();

//     if(empty($queryParams['senderNumber']) || empty($queryParams['receiverNumber']) || empty($queryParams['message']))
//     {
//         return new Response(
//             400,
//             array(
//                 'Content-Type' => 'application/json'
//             ),""
//         );
//     }

//     $sender = $queryParams['senderNumber'];
//     $receiver = $queryParams['receiverNumber'];
//     $message = $queryParams['message'];



//     $message = 'First Message';
//     $encodedMessage = GsmEncoder::utf8_to_gsm0338($message);
//     $send = new SmppAddress( 'Gadha',SMPP::TON_ALPHANUMERIC );
//     $to = new SmppAddress( "962795833193",SMPP::TON_INTERNATIONAL,SMPP::NPI_E164 );
//     $smpp->sendSMS( $from,$to,$encodedMessage,null );

//     #TODO Send Message

//     return new Response(
//         200,
//         array(
//             'Content-Type' => 'application/json'
//         ),
//         json_encode($queryParams)
//     );
// });

// $socket = new React\Socket\Server('0.0.0.0:49155', $loop);
// $server->listen($socket);