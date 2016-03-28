<?php
require_once __DIR__ . '/../vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
$channel = $connection->channel();

$queue = 'msgs';
$channel->queue_declare($queue, false, true, false, false);

$the_msg = "test msg";
$msg = new AMQPMessage($the_msg);
$channel->basic_publish($msg, '', $queue);

echo "\n----------\n";
echo "Sent $the_msg\n";
echo "----------\n";

$channel->close();
$connection->close();
