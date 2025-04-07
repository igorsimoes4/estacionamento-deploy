<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;

class WebSocketController implements MessageComponentInterface
{
    protected $clients;

    public function __construct()
    {
        $this->clients = new \SplObjectStorage;
        Log::info('WebSocketController inicializado');
    }

    public function onOpen(ConnectionInterface $conn)
    {
        $this->clients->attach($conn);
        Log::info('Nova conexão WebSocket estabelecida', ['id' => $conn->resourceId]);
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        Log::info('Mensagem WebSocket recebida', ['message' => $msg]);
        
        foreach ($this->clients as $client) {
            if ($from !== $client) {
                $client->send($msg);
            }
        }
    }

    public function onClose(ConnectionInterface $conn)
    {
        $this->clients->detach($conn);
        Log::info('Conexão WebSocket fechada', ['id' => $conn->resourceId]);
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        Log::error('Erro na conexão WebSocket', ['error' => $e->getMessage()]);
        $conn->close();
    }
} 