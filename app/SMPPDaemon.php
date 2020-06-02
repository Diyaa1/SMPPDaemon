<?php

use React\Http\Server;
use React\Http\Response;
use Psr\Http\Message\ServerRequestInterface;

function printDebug($str) {
    $log_filename = "log";
    if (!file_exists($log_filename)) 
    {
        // create directory/folder uploads.
        mkdir($log_filename, 0777, true);
    }
    $log_file_data = $log_filename.'/log_low_' . date('d-M-Y') . '.log';
    // if you don't add `FILE_APPEND`, the file will be erased each time you add a log
    file_put_contents($log_file_data, $str . "\n", FILE_APPEND);
}

$smpp = Helpers::getSMPPConnection();
$loop = React\EventLoop\Factory::create();



//Port 49155
$server = new Server(function (ServerRequestInterface $request) use( &$smpp ) {

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



  $sender = $queryParams['senderNumber'];
  $receiver = $queryParams['receiverNumber'];
  $message = $queryParams['message'];

  Helpers::wh_log('Send Request To Number ' . $receiver );
  
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
      json_encode(['code' => 0])
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
