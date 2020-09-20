<?php

use React\Http\Server;
use React\Http\Response;
use Psr\Http\Message\ServerRequestInterface;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

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

$logger = new Logger('global_logger');
$logger->pushHandler(new StreamHandler('log/main.log', Logger::INFO));

$smpp = SMPPUtils::getSMPPConnection();
$loop = React\EventLoop\Factory::create();

//Port 49155
$server = new Server(function (ServerRequestInterface $request) use( &$smpp, $logger ) {

  $queryParams = $request->getQueryParams();
  $serverParams = $request->getServerParams();
  $postParams = $request->getParsedBody();

  $path = $request->getUri()->getPath();

  /*
   * Check if server is alive
   */
  if( strpos($path, 'getStatus') !== false ){
    return new Response(
        200,
        array('Content-Type' => 'application/json'),
        json_encode(['code' => 0 ])
    );
  }

  /**
   * Send a group of messages
   */
  if( strpos($path, 'bulk') !== false ){
    return BulkSms::send_bulk( $smpp, $postParams  );
  }

  /**
   * UnRecongnized Request
   */
  if(empty($queryParams['senderNumber']) || empty($queryParams['receiverNumber']) || empty($queryParams['message']))
  {

    $logger->error("Bad Request From REMOTE ADDRESS", [
        "remote_ip" => $serverParams['REMOTE_ADDR']
    ]);

    return new Response(
          200,
          array(
              'Content-Type' => 'application/json'
          ),"ERROR"
    );
  }


 /**
  *  Default request
  */
  $sender = $queryParams['senderNumber'];
  $receiver = $queryParams['receiverNumber'];
  $message = $queryParams['message'];

  $encodedMessage = mb_convert_encoding($message,'UTF-8','UCS-2');
  $encodedMessage = iconv('utf-8', "UCS-2BE", $message);

  $sender = new SmppAddress( $sender,SMPP::TON_ALPHANUMERIC );
  $reciver = new SmppAddress( $receiver ,SMPP::TON_INTERNATIONAL,SMPP::NPI_E164 );
  try{
    $response = $smpp->sendSMS( $sender,$reciver, $encodedMessage, null, SMPP::DATA_CODING_UCS2, 0x01 );

    if( !$response ){
        throw new Exception();
    }

    $logger->info("Send Request To Number", [
        "sender"   => $sender,
        "receiver" => $reciver,
        "msg"      => $encodedMessage
    ]);
  }catch(Exception $e){
    $logger->warning("Failed Send Request To Number", [
        "sender"   => $sender,
        "receiver" => $reciver,
        "msg"      => $encodedMessage
    ]);
    return new Response(
        500,
        array('Content-Type' => 'application/json'),
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

$socket = new React\Socket\Server($_ENV['LISTEN_TO'], $loop);
$server->listen($socket);

//Peridcally send enquiry command
$loop->addPeriodicTimer(5, function () use (&$smpp, $logger) {
    try{
        $smpp->respondEnquireLink();
        $smpp->enquireLink();
    } catch(Exception $e){
        $logger->error("Connection to the smpp server is lost trying to connect...");
	    $smpp = SMPPUtils::getSMPPConnection();
    }
});

$loop->run();
