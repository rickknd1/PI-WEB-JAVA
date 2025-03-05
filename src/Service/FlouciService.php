<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class FlouciService
{
    private HttpClientInterface $client;
    private string $appToken;
    private string $appSecret;

    public function __construct(HttpClientInterface $client, string $appToken, string $appSecret)
    {
        $this->client = $client;
        $this->appToken = $appToken;
        $this->appSecret = $appSecret;
    }

    public function generatePayment(float $amount, string $successLink, string $failLink, string $trackingId): array
    {
        $response = $this->client->request('POST', 'https://developers.flouci.com/api/generate_payment', [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'app_token' => $this->appToken,
                'app_secret' => $this->appSecret,
                'accept_card' => true,
                'amount' => $amount,
                'success_link' => $successLink,
                'fail_link' => $failLink,
                'session_timeout_secs' => 1200,
                'developer_tracking_id' => $trackingId,
            ],
        ]);

        return $response->toArray();
    }
}