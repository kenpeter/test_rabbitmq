<?php
include(__DIR__ . '/../config.php');

use PhpAmqpLib\Connection\AMQPStreamConnection;

$exchange = 'router';
$queue = 'msgs';
$consumer_id = 'consumer';
$connection = new AMQPStreamConnection(HOST, PORT, USER, PASS, VHOST);

$channel = $connection->channel();

/*
    name: $queue
    passive: false
    durable: true // the queue will survive server restarts
    exclusive: false // the queue can be accessed in other channels
    auto_delete: false //the queue won't be deleted once the channel is closed.
*/
$channel->queue_declare($queue, false, true, false, false);

/*
    name: $exchange
    type: direct
    passive: false
    durable: true // the exchange will survive server restarts
    auto_delete: false //the exchange won't be deleted once the channel is closed.
*/
$channel->exchange_declare($exchange, 'direct', false, true, false);


$channel->queue_bind($queue, $exchange);



/**
 * @param \PhpAmqpLib\Message\AMQPMessage $message
 */
// Here how we consume
// We can have multiple of these.
function process_message($message)
{
    echo "\n--------\n";
    echo $message->body;
    echo "\n--------\n";

    // msg to channel, sent, then ack, tag the ack
    $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);

    // Send a message with the string "quit" to cancel the consumer.
    if ($message->body === 'quit') {
        $message->delivery_info['channel']->basic_cancel($message->delivery_info['consumer_tag']);
    }
}


/*
    queue: Queue from where to get the messages
    consumer_tag: Consumer identifier
    no_local: Don't receive messages published by this consumer. (local)
    no_ack: Tells the server if the consumer will acknowledge the messages. (ack)
    exclusive: Request exclusive consumer access, meaning only this consumer can access the queue (no exclusive)
    nowait: (need to wait)
    callback: A PHP Callback
*/
$channel->basic_consume($queue, $consumer_id, false, false, false, false, 'process_message');

/**
 * @param \PhpAmqpLib\Channel\AMQPChannel $channel
 * @param \PhpAmqpLib\Connection\AbstractConnection $connection
 */
function shutdown($channel, $connection)
{
  $channel->close();
  $connection->close();
}

// This script finishes, then call back
// $channel, $connection are args
register_shutdown_function('shutdown', $channel, $connection);

// Loop as long as the channel has callbacks registered
// $channel->basic_consume can register multiple callbacks, here we keep looping the call back.
while (count($channel->callbacks)) {
  $channel->wait();
}
