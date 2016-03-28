<?php
require_once __DIR__ . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
$channel = $connection->channel();
$channel->queue_declare('hello4', false, false, false, false);
$msg = new AMQPMessage('new msg, new channel');
$channel->basic_publish($msg, '', 'hello4');
echo " [x] Sent 'new msg, new channel'\n";
$channel->close();
$connection->close();
