<?php

use React\Http\Server;
use React\Http\Response;
use Psr\Http\Message\ServerRequestInterface;

function printDebug($str) {
		echo date('Ymd H:i:s ').$str."\r\n";
}

$smpp = Helpers::getSMPPConnection();
$loop = React\EventLoop\Factory::create();



//Port 49155
$server = new Server(function (ServerRequestInterface $request) use( $smpp ) {

  $queryParams = $request->getQueryParams();

  $path = $request->getUri()->getPath();

  if( strpos($path, 'getStatus') !== false ){
    return new Response(
        200,
        array('Content-Type' => 'application/json'),
        json_encode(['code' => 0 ])
    );
  }

  if(empty($queryParams['senderNumber']) || empty($queryParams['receiverNumber']) || empty($queryParams['message']))
  {
      return new Response(
          200,
          array(
              'Content-Type' => 'application/json'
          ),"ERROR"
      );
  }

  Helpers::wh_log('Send Request');

  $sender = $queryParams['senderNumber'];
  $receiver = $queryParams['receiverNumber'];
  $message = $queryParams['message'];
  
  $encodedMessage = mb_convert_encoding($message,'UTF-8','UCS-2');
  $encodedMessage = iconv('utf-8', "UCS-2BE", $message);
  
  $sender = new SmppAddress( $sender,SMPP::TON_ALPHANUMERIC );
  $reciver = new SmppAddress( $receiver ,SMPP::TON_INTERNATIONAL,SMPP::NPI_E164 );
  try{
   $response = $smpp->sendSMS( $sender,$reciver, $encodedMessage, null, SMPP::DATA_CODING_UCS2, 0x01 );
   Helpers::wh_log('Success');
  }catch(Exception $e){
    Helpers::wh_log('failed');
    return new Response(
	500,
	array(
		'Content-Type' => 'application/json'
	),
	json_encode(['code' => 500])
     );
  }
  return new Response(
      200,
      array(
          'Content-Type' => 'application/json'
      ),
      json_encode($response)
  );
});

$socket = new React\Socket\Server('0.0.0.0:49155', $loop);
$server->listen($socket);

//Peridcally send enquiry command
$loop->addPeriodicTimer(5, function () use (&$smpp) {
    try{
        $smpp->respondEnquireLink();
        $smpp->enquireLink();
    } catch(Exception $e){
	    $smpp = Helpers::getSMPPConnection();
    }
});

$loop->run();
