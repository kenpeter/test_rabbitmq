<?php
//chdir(dirname(__DIR__));
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/SimpleReceiver.php';


use Acme\AmqpWrapper\SimpleReceiver;
$receiver = new SimpleReceiver();
$receiver->listen();
