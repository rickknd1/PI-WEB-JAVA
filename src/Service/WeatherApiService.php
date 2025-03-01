<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
class WeatherApiService
{
    private HttpClientInterface $client;
    private string $apiKey = '98c767a159bd49d6b56170140252802';

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    public function getFutureWeather(string $city, string $date): array
    {
        $currentDate = new \DateTime();
        if($date < $currentDate){
            $url = sprintf('http://api.weatherapi.com/v1/history.json?key=%s&q=%s&dt=%s',
                $this->apiKey,
                $city,
                $date
            );
        }else{
            $url = sprintf('http://api.weatherapi.com/v1/future.json?key=%s&q=%s&dt=%s',
                $this->apiKey,
                $city,
                $date
            );
        }

        $response = $this->client->request('GET', $url);
        $data = $response->toArray();

        return $data['forecast'] ?? [];
    }
}