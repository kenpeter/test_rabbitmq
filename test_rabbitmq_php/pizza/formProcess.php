<?php
//chdir(dirname(__DIR__));

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/SimpleSender.php';

use Acme\AmqpWrapper\SimpleSender;

// https://secure.php.net/manual/en/function.filter-input.php
// post the var: theName
$theName = filter_input(INPUT_POST, 'theName', FILTER_SANITIZE_STRING);

$simpleSender = new SimpleSender();

$simpleSender->execute($theName);

header("Location: http://localhost/test/testme/test_rabbitmq/test_rabbitmq_php/pizza/orderReceived.html");
