<?php

require_once __DIR__ . '/../vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
$channel = $connection->channel();

$queue = "rpc_queue";

$channel->queue_declare($queue, false, false, false, false);

function fib($n) {
  if ($n == 0)
      return 0;
  if ($n == 1)
      return 1;
  return fib($n-1) + fib($n-2);
}

echo " [x] Awaiting RPC requests\n";

$callback = function($req) {
  $n = intval($req->body);
  echo " [.] fib(", $n, ")\n";

  // pass the func as payload
  // 
  $msg = new AMQPMessage(
    (string) fib($n),
    array('correlation_id' => $req->get('correlation_id'))
  );

  // req attaches a channel, then can do publish
  // $msg, '' is exchange, then reply_to
  // $req->get, can get all extra parts.
  // $req->get('correlation_id')
  // $req->get('reply_to') 
  $req->delivery_info['channel']->basic_publish(
    $msg, '', $req->get('reply_to')
  );

  // channel, ack, delivery_tag contains all done, or not
  $req->delivery_info['channel']->basic_ack(
    $req->delivery_info['delivery_tag']
  );

};

// qos quality of service
$channel->basic_qos(null, 1, null);

// The callback will receive $req argument
$channel->basic_consume($queue, '', false, false, false, false, $callback);

while(count($channel->callbacks)) {
  $channel->wait();
}

$channel->close();
$connection->close();
