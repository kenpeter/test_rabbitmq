<?php
require_once __DIR__ . '/../vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
$channel = $connection->channel();

$queue = "task_queue";


$channel->queue_declare($queue, false, false, false, false);

$data = implode(' ', array_slice($argv, 1));

if(empty($data)) 
  $data = "hi ..";

$msg = new AMQPMessage(
  $data,
  array('delivery_mode' => 2) # make message persistent
);

$channel->basic_publish($msg, '', $queue);



$channel->close();
$connection->close();
