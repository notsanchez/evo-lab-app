<?php
require __DIR__ . '/vendor/autoload.php';

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;

class TranscriptionServer implements MessageComponentInterface
{
    /** @var \SplObjectStorage<ConnectionInterface> */
    protected \SplObjectStorage $clients;
    /** @var array<string,\SplObjectStorage<ConnectionInterface>> */
    protected array $rooms = [];

    public function __construct()
    {
        $this->clients = new \SplObjectStorage;
        echo "Servidor WebSocket em ws://localhost:8080\n";
    }

    public function onOpen(ConnectionInterface $conn)
    {
        $this->clients->attach($conn);
    }

    public function onClose(ConnectionInterface $conn)
    {
        $this->removeFromRoom($conn);
        $this->clients->detach($conn);
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        $this->removeFromRoom($conn);
        $conn->close();
    }


    public function onMessage(ConnectionInterface $from, $msg)
    {
        $data = json_decode($msg, true);

        if (isset($data['join'])) {
            $room = preg_replace('/[^\w\-]/', '', $data['join']);
            $this->addToRoom($from, $room);
            return;
        }

        if (empty($from->room) || !isset($this->rooms[$from->room])) {
            return;
        }

        if (empty($data['text'])) return;

        foreach ($this->rooms[$from->room] as $client) {
            $client->send($msg);
        }
    }

    protected function broadcastViewers(string $room): void
    {
        if (!isset($this->rooms[$room])) return;

        $payload = json_encode(['viewers' => count($this->rooms[$room])]);

        foreach ($this->rooms[$room] as $client) {
            $client->send($payload);
        }
    }

    protected function addToRoom(ConnectionInterface $conn, string $room): void
    {
        $this->removeFromRoom($conn);

        $this->rooms[$room] ??= new \SplObjectStorage;
        $this->rooms[$room]->attach($conn);
        $conn->room = $room;

        $this->broadcastViewers($room);
    }


    protected function removeFromRoom(ConnectionInterface $conn): void
    {
        if (!empty($conn->room) && isset($this->rooms[$conn->room])) {
            $this->rooms[$conn->room]->detach($conn);
            $this->broadcastViewers($conn->room);

            if (!count($this->rooms[$conn->room])) unset($this->rooms[$conn->room]);
            unset($conn->room);
        }
    }
}

$server = IoServer::factory(
    new HttpServer(new WsServer(new TranscriptionServer())),
    8080
);
$server->run();