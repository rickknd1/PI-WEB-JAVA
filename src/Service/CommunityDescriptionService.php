<?php

namespace App\Service;

use Gemini\Client;

class CommunityDescriptionService
{
    private Client $geminiClient;

    public function __construct(Client $geminiClient)
    {
        $this->geminiClient = $geminiClient;
    }

    public function traiterDemande(string $message): string
    {
        $result = $this->geminiClient->geminiFlash()->generateContent($message);
        return $result->text();
    }
}
