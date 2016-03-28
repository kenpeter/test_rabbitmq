<?php
namespace Acme\AmqpWrapper;

use PhpAmqpLib\Connection\AMQPConnection;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class SimpleReceiver
{
    /**
     * @var Logger
     */
    private $pizzaLog;

    /**
     * @var Logger
     */
    private $log;
    
    public function __construct()
    {
        // Pizza logger
        $this->pizzaLog = new Logger('pizzas');
        $this->pizzaLog->pushHandler(new StreamHandler('logs/pizza.log', Logger::INFO));
        
        // receiver logger
        $this->log = new Logger('simpleReceive');
        $this->log->pushHandler(new StreamHandler('logs/simpleReceive.log', Logger::INFO));
    }

    /**
     * Listens for incoming messages
     */
    public function listen()
    {
        // Add log, start to listen 
        $this->log->addInfo('Start listening routine');
        
        // Connection
        $connection = new AMQPConnection(
            'localhost',    #host 
            5672,           #port
            'guest',        #user
            'guest'         #password
            );

        // Channel
        $channel = $connection->channel();
        
        // queue
        $channel->queue_declare(
            'pizzaTime',    #queue name, the same as the sender
            false,          #passive
            false,          #durable
            false,          #exclusive
            false           #autodelete
            );
        
        // route key
        $channel->basic_consume(
            'pizzaTime',            #queue 
            '',                     #consumer tag
            false,                  #no local
            true,                   #no ack
            false,                  #exclusive
            false,                  #no wait
            array($this, 'addLog')  #callback
            );
            
        $this->log->addInfo('Consuming from channel');
        
        // Looping
        while(count($channel->callbacks)) {
            $channel->wait();
        }
        
        $channel->close();
        $connection->close();
    }

    /**
     * @param $msg
     */
    // The call back, so add 2 logs
    public function addLog($msg)
    {
        $this->log->addInfo('Received ' . $msg->body);
        $this->pizzaLog->addInfo($msg->body);
    }
}
