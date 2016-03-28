<?php
namespace Acme\AmqpWrapper;

use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class SimpleSender
{
    /**
     * @var Logger
     */
    private $log;
    
    public function __construct()
    {
        // create send logger
        // define the log file simpleSend.log
        $this->log = new Logger('simpleSend');
        $this->log->pushHandler(new StreamHandler('logs/simpleSend.log', Logger::INFO));
    }
    
    /**
     * Sends a message to the pizzaTime queue.
     * 
     * @param string $message
     */
    public function execute($message)
    {
        // log->addInfo, add log info   
        $this->log->addInfo('Received message to send: ' . $message);
        
        // Connection
        $connection = new AMQPConnection(
            'localhost',    #host 
            5672,           #port
            'guest',        #user
            'guest'         #password
            );


        /** @var $channel AMQPChannel */
        // Channel
        $channel = $connection->channel();
        
        // Channel -> queue
        // Define routing key as queue.
        $channel->queue_declare(
            'pizzaTime',    #queue name
            false,          #passive
            false,          #durable
            false,          #exclusive
            false           #autodelete
            );
        
        // Msg
        $msg = new AMQPMessage($message);
        
        // the queue is the routing key??
        $channel->basic_publish(
            $msg,           #message 
            '',             #exchange
            'pizzaTime'     #routing key
            );
            
        $this->log->addInfo('Message sent');
        
        // close channel, then connection
        $channel->close();
        $connection->close();
    }
}
