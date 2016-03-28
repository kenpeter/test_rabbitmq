<?php

include(__DIR__ . "/../config.php");
use PhpAmqpLib\Connection\AMQPStreamConnection;

$exchange = 'fanout_exclusive_example_exchange';

$queue = ''; // if empty let RabbitMQ create a queue name


// set a queue name and run multiple instances
// to test exclusiveness
$consumerTag = 'consumer' . getmypid();
$connection = new AMQPStreamConnection(HOST, PORT, USER, PASS, VHOST);

// Connection gets channel
$channel = $connection->channel();

/*
    name: $queue    // should be unique in fanout exchange. Let RabbitMQ create
                    // a queue name for us

    // no duplicated queue
    passive: false  // don't check if a queue with the same name exists


    durable: false // the queue will not survive server restarts
    exclusive: true // the queue can not be accessed by other channels
    auto_delete: true //the queue will be deleted once the channel is closed.
*/
list($queueName, ,) = $channel->queue_declare($queue, false, false, true, true);

/*
    name: $exchange
    type: direct
    passive: false // don't check if a exchange with the same name exists
    durable: false // the exchange will not survive server restarts
    auto_delete: true //the exchange will be deleted once the channel is closed.
*/
$channel->exchange_declare($exchange, 'fanout', false, false, true);


