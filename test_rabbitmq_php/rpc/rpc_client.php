<?php

require_once __DIR__ . '/../vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class FibonacciRpcClient {
  private $connection;
  private $channel;
  private $callback_queue;
  private $response;
  private $corr_id;

  public function __construct() {
    $this->connection = new AMQPStreamConnection(
      'localhost', 5672, 'guest', 'guest');

    $this->channel = $this->connection->channel();

    list($this->callback_queue, ,) = $this->channel->queue_declare(
      "", false, false, true, false);

    // basic consume, can attach multiple callbacks
    $this->channel->basic_consume(
      $this->callback_queue, '', false, false, false, false,
      array($this, 'on_response')
    );

  }

  public function on_response($rep) {
    if($rep->get('correlation_id') == $this->corr_id) {

      // The body is the response
      $this->response = $rep->body;
    }
  }

  // Entry point
  public function call($n) {
    $this->response = null;
    $this->corr_id = uniqid();

    // payload is $n, 
    // $req->body === $n for the server
    // 2nd part is anything

    // $this->callback_queue is just queue
    $msg = new AMQPMessage(
      (string) $n,
      array('correlation_id' => $this->corr_id,
            'reply_to' => $this->callback_queue)
      );

    // Send question, then wait for answers
    $this->channel->basic_publish($msg, '', 'rpc_queue');

    while(!$this->response) {
      $this->channel->wait();
    }
    return intval($this->response);
  }

};

$fibonacci_rpc = new FibonacciRpcClient();

// Here we execute
$response = $fibonacci_rpc->call(30);

echo " [.] Got ", $response, "\n";

