<?php
namespace Corley\Queue\RabbitMQ;

use Corley\Queue\RabbitMQ\RabbitMQ;
use PhpAmqpLib\Connection\AMQPStreamConnection;

class RabbitMQTest extends \PHPUnit_Framework_TestCase
{
    private $conn;

    public function setUp()
    {
        $this->conn = new AMQPStreamConnection('localhost', 5672, 'wdalmut', 'testtest');
    }

    public function testBasePublish()
    {
        $rabbit = new RabbitMQ($this->conn);

        $rabbit->send('testqueue', 'test', []);
    }

    public function testSendReceive()
    {
        $rabbit = new RabbitMQ($this->conn);

        $rabbit->send('testqueue', 'test', []);
        list($receipt, $message) = $rabbit->receive("testqueue", []);

        $this->assertNotNull($receipt);
        $this->assertEquals("test", $message);
    }

    public function testSendReceiveAndDelete()
    {
        $rabbit = new RabbitMQ($this->conn);

        $rabbit->send('testqueue', 'test', []);
        list($receipt, $message) = $rabbit->receive("testqueue", []);
        $rabbit->delete("testqueue", $receipt, []);
    }

    public function testReceiveTimeoutUnlock()
    {
        $rabbit = new RabbitMQ($this->conn, ["receive_timeout" => 1]);

        list($receipt, $message) = $rabbit->receive("emptyqueue", []);
        $this->assertFalse($receipt);
        $this->assertFalse($message);
    }
}
