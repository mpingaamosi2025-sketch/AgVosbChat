<?php

error_reporting(E_ALL & ~E_DEPRECATED);

require __DIR__ . '/vendor/autoload.php';

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class Chat implements MessageComponentInterface {

    protected $clients;
    public function __construct() {
        $this->clients = new SplObjectStorage;
        echo "WebSocket Server Started...\n";
    }
    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
        echo "New connection: {$conn->resourceId}\n";
    }
    public function onMessage(ConnectionInterface $from, $msg) {
        foreach ($this->clients as $client) {
            if ($from !== $client) {
                $client->send($msg);
            }
        }
    }
    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);
        echo "Disconnected: {$conn->resourceId}\n";
    }
    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "Error: {$e->getMessage()}\n";
        $conn->close();
    }
}
$server = Ratchet\Server\IoServer::factory(
    new Ratchet\Http\HttpServer(
        new Ratchet\WebSocket\WsServer(
            new Chat()
        )
    ),
    8080
);
$server->run();