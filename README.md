# RabbitMQ adapter

To use this package your need `php-amqplib/php-amqplib`

```sh
composer require php-amqplib/php-amqplib:~2
```

## Use as adapter

Create the adapter

```php
use Corley\Queue\RabbitMQ\RabbitMQ;

$amqp = new AMQPStreamConnection('localhost', 5672, 'username', 'password');
$adapter = new RabbitMQ($amqp);
```

You can use `exchange` and receive timeout options

```php
$adapter = new RabbitMQ($amqp, [
    "exchange" => "my_exchange", // send to an exchange
    "receive_timeout" => 20, // exit after 20 seconds
]);
```

Set as usual

```php
use Corley\Queue\Queue;

$queue = new Queue("my_queue", $adapter);
$queue->send(json_encode(["test" => "ok"]));

list($receipt, $message) = $queue->receive();
$message = json_decode($message, true);

$queue->delete($receipt);
```

## Test suite (functional)

```sh
docker run -d --hostname my-rabbit \
    -p 5672:5672 -p 15672:15672 --name rabbit \
    -e RABBITMQ_DEFAULT_USER=wdalmut \
    -e RABBITMQ_DEFAULT_PASS=testtest \
    rabbitmq:3-management
```

