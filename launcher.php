<?php

require __DIR__.'/vendor/autoload.php';

require_once('app/SMPP/sockettransport.class.php');
require_once('app/SMPP/smppclient.class.php');
require_once('app/SMPP/gsmencoder.class.php');
require_once('app/Helpers.php');
require_once('app/BulkSms.php');

define( 'LAN_DIR',__DIR__ );

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();


require_once 'app/SMPPDaemon.php';
