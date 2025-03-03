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
        $dateObject = new \DateTime($date);

        $interval = $currentDate->diff($dateObject);
        $days = $interval->days;

        if ($dateObject < $currentDate) {
            $url = sprintf(
                'http://api.weatherapi.com/v1/history.json?key=%s&q=%s&dt=%s',
                $this->apiKey,
                $city,
                $date
            );
        } elseif ($days < 15) {
            $days = max($days, 14);
            $url = sprintf(
                'http://api.weatherapi.com/v1/forecast.json?key=%s&q=%s&days=%s&aqi=no&alerts=no',
                $this->apiKey,
                $city,
                $days
            );
        } else {
            $url = sprintf(
                'http://api.weatherapi.com/v1/future.json?key=%s&q=%s&dt=%s',
                $this->apiKey,
                $city,
                $date
            );
        }

        try {
            $response = $this->client->request('GET', $url);
            $data = $response->toArray();
        } catch (\Exception $e) {
            return [];
        }

        return $data['forecast'] ?? [];
    }

}