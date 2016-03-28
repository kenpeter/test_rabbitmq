<?php
require_once __DIR__ . '/../vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
$channel = $connection->channel();

$queue = "task_queue"; 

$channel->queue_declare($queue, false, false, false, false);
echo ' [*] Waiting for messages. To exit press CTRL+C', "\n";


/*
$callback = function($msg) {
  echo " [x] Received ", $msg->body, "\n";
};
$channel->basic_consume('hello4', '', false, true, false, false, $callback);
*/



$callback = function($msg){
  echo " [x] Received ", $msg->body, "\n";
  sleep(substr_count($msg->body, '.'));
  echo " [x] Done", "\n";
  $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
};

$channel->basic_qos(null, 1, null);
$channel->basic_consume($queue, '', false, false, false, false, $callback);



while(count($channel->callbacks)) {
    $channel->wait();
}




$channel->close();
$connection->close();
