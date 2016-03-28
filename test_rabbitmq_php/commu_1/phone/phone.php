<?php

require_once __DIR__ . '/../../vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class PhoneClient {
  private $connection;
  private $channel;
  private $callback_queue;
  private $response;
  private $corr_id;

  private $task_queue;

  public function __construct($task_queue) {
    $this->task_queue = $task_queue;

    // Connection
    $this->connection = new AMQPStreamConnection(
      'localhost', 5672, 'guest', 'guest');

    // Connection -> channel
    $this->channel = $this->connection->channel();

    // delare queue, no exchange
    // also get the queue, after declare
    // $this->callback_queue, also called responsed queue, we don't care what queue
    list($this->callback_queue, ,) = $this->channel->queue_declare(
        "", false, false, true, false);

    // once the route, the queue, consume it. give it multiple callbacks.
    // why passes this? because on_response needs to use $this
    $this->channel->basic_consume(
      $this->callback_queue, '', false, false, false, false,
      array($this, 'on_response'));

  }

  public function on_response($rep) {
    // correlation _id == 
    if($rep->get('correlation_id') == $this->corr_id) {
      // Get it feedback from the server
      $this->response = $rep->body;
    }
  }

  public function call($question) {

    $this->response = null;

    // correlation id
    $this->corr_id = uniqid();

    // $n is payload
    // server please write down the id you response to and
    // reply this queue
    $msg = new AMQPMessage(
      (string) $question, // it asks the question
      array(
        'correlation_id' => $this->corr_id,
        'reply_to' => $this->callback_queue
      )
    );

    $this->channel->basic_publish($msg, '', $this->task_queue);

    // Waiting for response
    while(!$this->response) {
      $this->channel->wait();
    }

    // Return result
    return $this->response;
  }
};

$fibonacci_rpc = new PhoneClient("gateway_q");

$question = "What is for dinner?";
$response = $fibonacci_rpc->call($question);

echo " [.] Got ", $response, "\n";
