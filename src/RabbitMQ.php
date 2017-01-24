<?php
namespace Corley\Queue\RabbitMQ;

use Corley\Queue\QueueInterface;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Exception\AMQPTimeoutException;

class RabbitMQ implements QueueInterface
{
    private $channel;
    private $options = [];

    public function __construct(AMQPStreamConnection $conn, array $options = [])
    {
        $this->channel = $conn->channel();
        $this->options = array_replace_recursive([
            "exchange" => "",
            "receive_timeout" => 20,
        ], $options);
    }

    public function getChannel()
    {
        return $this->channel;
    }

    public function send($queueName, $message, array $options)
	{
        $message = new AMQPMessage($message, $options);

        $this->channel->basic_publish($message, $this->options["exchange"], $queueName);
    }

    public function receive($queueName, array $options)
    {
        $tag = '';
        $response = [false, false];
        $tag = $this->channel->basic_consume($queueName, '', false, false, false, false, function($req) use (&$tag, &$response) {
            $response = [$req->delivery_info['delivery_tag'], $req->body];
            $this->channel->basic_cancel($tag);
        });

        try {
            while(count($this->channel->callbacks)) {
                $this->channel->wait(null, false, $this->options["receive_timeout"]);
            }
        } catch (AMQPTimeoutException $e) {}

        return $response;
    }

    public function delete($queueName, $receipt, array $options)
    {
        $this->channel->basic_ack($receipt);
    }
}
