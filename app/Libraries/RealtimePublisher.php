<?php

namespace App\Libraries;

class RealtimePublisher
{
    public function publishReplyCreated(int $ticketId, int $actorId, string $audience = 'all'): void
    {
        $this->publish([
            'type'     => 'reply.created',
            'ticketId' => $ticketId,
            'actorId'  => $actorId,
            'audience' => $audience,
        ]);
    }

    public function makeSubscriptionData(int $ticketId, int $userId, string $role, string $browserUrl): array
    {
        $config = config('Realtime');
        $expiresAt = time() + 3600;
        $payload = implode('|', [$userId, $role, $ticketId, $expiresAt]);

        return [
            'enabled'      => true,
            'ticketId'     => $ticketId,
            'currentUserId'=> $userId,
            'currentRole'  => $role,
            'wsUrl'        => $browserUrl,
            'subscription' => [
                'ticketId'  => $ticketId,
                'userId'    => $userId,
                'role'      => $role,
                'expiresAt' => $expiresAt,
                'signature' => hash_hmac('sha256', $payload, $config->secret),
            ],
        ];
    }

    private function publish(array $payload): void
    {
        $config = config('Realtime');
        $url = 'http://' . $config->publishHost . ':' . $config->publishPort . '/publish';

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'X-Realtime-Secret: ' . $config->secret,
            ],
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT_MS     => 1200,
            CURLOPT_CONNECTTIMEOUT_MS => 500,
        ]);

        $response = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($response === false || ($httpCode !== 200 && $httpCode !== 202)) {
            log_message('debug', 'Realtime publish skipped: HTTP {code}; {error}', [
                'code'  => $httpCode,
                'error' => $error ?: 'no response',
            ]);
        }
    }
}
