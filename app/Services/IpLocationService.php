<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class IpLocationService
{
    public function resolve(?string $ipAddress): array
    {
        if (blank($ipAddress) || !filter_var($ipAddress, FILTER_VALIDATE_IP)) {
            return $this->unknown();
        }

        if (!filter_var($ipAddress, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            return [
                'city' => 'Local Network',
                'country' => 'Private',
                'label' => 'Local Network, Private',
            ];
        }

        return Cache::remember(
            'ip-location:' . $ipAddress,
            now()->addDays(7),
            function () use ($ipAddress) {
                try {
                    $response = Http::acceptJson()
                        ->timeout(3)
                        ->get('https://ipwho.is/' . $ipAddress);

                    if (!$response->ok()) {
                        return $this->unknown();
                    }

                    $payload = $response->json();

                    if (!is_array($payload) || !($payload['success'] ?? false)) {
                        return $this->unknown();
                    }

                    $city = trim((string) ($payload['city'] ?? ''));
                    $country = trim((string) ($payload['country'] ?? ''));

                    if ($city === '' && $country === '') {
                        return $this->unknown();
                    }

                    if ($city === '') {
                        $city = 'Unknown City';
                    }

                    if ($country === '') {
                        $country = 'Unknown Country';
                    }

                    return [
                        'city' => $city,
                        'country' => $country,
                        'label' => $city . ', ' . $country,
                    ];
                } catch (\Throwable $exception) {
                    return $this->unknown();
                }
            }
        );
    }

    protected function unknown(): array
    {
        return [
            'city' => 'Unknown City',
            'country' => 'Unknown Country',
            'label' => 'Unknown City, Unknown Country',
        ];
    }
}
