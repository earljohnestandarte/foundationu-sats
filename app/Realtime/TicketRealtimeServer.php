<?php

namespace App\Realtime;

use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;
use SplObjectStorage;

class TicketRealtimeServer implements MessageComponentInterface
{
    private SplObjectStorage $clients;

    public function __construct(private readonly \Config\Realtime $config)
    {
        $this->clients = new SplObjectStorage();
    }

    public function onOpen(ConnectionInterface $conn): void
    {
        $this->clients->attach($conn, null);
    }

    public function onClose(ConnectionInterface $conn): void
    {
        if ($this->clients->contains($conn)) {
            $this->clients->detach($conn);
        }
    }

    public function onError(ConnectionInterface $conn, \Exception $e): void
    {
        log_message('error', 'Realtime socket error: {message}', ['message' => $e->getMessage()]);
        $conn->close();
    }

    public function onMessage(ConnectionInterface $from, $msg): void
    {
        $payload = json_decode((string) $msg, true);
        if (! is_array($payload)) {
            $from->send(json_encode(['type' => 'error', 'message' => 'Invalid message payload.']));
            return;
        }

        if (($payload['action'] ?? null) !== 'subscribe') {
            $from->send(json_encode(['type' => 'error', 'message' => 'Unsupported realtime action.']));
            return;
        }

        if (! $this->isValidSubscription($payload)) {
            $from->send(json_encode(['type' => 'error', 'message' => 'Subscription rejected.']));
            $from->close();
            return;
        }

        $this->clients[$from] = [
            'ticketId' => (int) $payload['ticketId'],
            'userId'   => (int) $payload['userId'],
            'role'     => (string) $payload['role'],
        ];

        $from->send(json_encode([
            'type'     => 'subscribed',
            'ticketId' => (int) $payload['ticketId'],
        ]));
    }

    public function publish(array $event): int
    {
        $ticketId = (int) ($event['ticketId'] ?? 0);
        if ($ticketId < 1) {
            return 0;
        }

        $audience = (string) ($event['audience'] ?? 'all');
        $sent = 0;

        foreach ($this->clients as $client) {
            $subscription = $this->clients[$client];
            if (! is_array($subscription)) {
                continue;
            }

            if ((int) ($subscription['ticketId'] ?? 0) !== $ticketId) {
                continue;
            }

            if ($audience === 'staff' && ! in_array($subscription['role'] ?? '', ['agent', 'sao', 'admin'], true)) {
                continue;
            }

            $client->send(json_encode($event));
            $sent++;
        }

        return $sent;
    }

    private function isValidSubscription(array $payload): bool
    {
        $ticketId = (int) ($payload['ticketId'] ?? 0);
        $userId = (int) ($payload['userId'] ?? 0);
        $role = (string) ($payload['role'] ?? '');
        $expiresAt = (int) ($payload['expiresAt'] ?? 0);
        $signature = (string) ($payload['signature'] ?? '');

        if ($ticketId < 1 || $userId < 1 || $expiresAt < time() || $signature === '') {
            return false;
        }

        if (! in_array($role, ['student', 'agent', 'sao', 'admin'], true)) {
            return false;
        }

        $expected = hash_hmac('sha256', implode('|', [$userId, $role, $ticketId, $expiresAt]), $this->config->secret);

        return hash_equals($expected, $signature);
    }
}
