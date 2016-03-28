<?php

require_once __DIR__ . '/../../vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
$channel = $connection->channel();

$task_queue = "gateway_q";

// What is wrong with druable
$channel->queue_declare($task_queue, false, true, false, false);

/*
function fib($n) {
  if ($n == 0)
    return 0;
  if ($n == 1)
    return 1;
  return fib($n-1) + fib($n-2);
}
*/

function give_answer($question) {
  return "$question: ". "chicken";
}

echo " [x] Awaiting RPC requests\n";

/*
// Here we receive the msg pack
$callback = function($req) {
  $n = intval($req->body); // the question itself

  echo " [.] fib(", $n, ")\n";

  // We resoled it.
  // Then send back the answer.
  // with correlation_id
  $msg = new AMQPMessage(
    (string) fib($n),
    array('correlation_id' => $req->get('correlation_id'))
    );

  // Via which queue, $req->get("reply_to");
  $req->delivery_info['channel']->basic_publish(
      $msg, '', $req->get('reply_to'));

  // delivery_tag as always
  $req->delivery_info['channel']->basic_ack(
      $req->delivery_info['delivery_tag']);
};
*/


// Here we receive the msg pack
$callback = function($req) {
  $question = $req->body; // the question itself

  echo " [.] somone asks this: $question\n";

  // We resoled it.
  // Then send back the answer.
  // with correlation_id
  $msg = new AMQPMessage(
    (string) give_answer($question),
    array('correlation_id' => $req->get('correlation_id'))
    );

  // Via which queue, $req->get("reply_to");
  $req->delivery_info['channel']->basic_publish(
      $msg, '', $req->get('reply_to'));

  // delivery_tag as always
  $req->delivery_info['channel']->basic_ack(
      $req->delivery_info['delivery_tag']);
};


$channel->basic_qos(null, 1, null);

$channel->basic_consume($task_queue, '', false, false, false, false, $callback);

// Waiting for incoming task
while(count($channel->callbacks)) {
  $channel->wait();
}

$channel->close();
$connection->close();

