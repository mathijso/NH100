<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WindDataService
{
    private const API_URL = 'https://api.open-meteo.com/v1/forecast';

    // Egmond aan Zee coördinaten: 52°37'02.6"N 4°37'04.7"E
    private const EGMOND_LAT = 52.624449;

    private const EGMOND_LON = 4.61718;

    // Het strand rijd je van Wijk aan Zee naar Bergen aan Zee, ongeveer richting NNO
    private const BEACH_HEADING = 10;

    // Midden van het strandvenster 10:30-12:00
    private const FORECAST_HOUR = '11:00';

    /**
     * Actuele omstandigheden plus de wind om 11:00 voor de komende 16 dagen.
     *
     * @return array{current: ?array, days: array<string, array>}
     */
    public function getWeather(): array
    {
        $empty = ['current' => null, 'days' => []];

        // Cache voor 15 minuten; nieuwe key sinds de verwachting erbij kwam
        return Cache::remember('weather_egmond_v2', 900, function () use ($empty) {
            try {
                $response = Http::timeout(5)->get(self::API_URL, [
                    'latitude' => self::EGMOND_LAT,
                    'longitude' => self::EGMOND_LON,
                    'current' => 'temperature_2m,wind_speed_10m,wind_direction_10m,wind_gusts_10m',
                    'hourly' => 'wind_speed_10m,wind_direction_10m,wind_gusts_10m',
                    'daily' => 'sunrise',
                    'timezone' => 'Europe/Amsterdam',
                    'forecast_days' => 16,
                ]);

                if ($response->successful()) {
                    return $this->process($response->json());
                }

                Log::error('Open-Meteo API error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
            } catch (\Exception $e) {
                Log::error('Error fetching weather data', ['message' => $e->getMessage()]);
            }

            return $empty;
        });
    }

    private function process(array $data): array
    {
        $current = null;

        if (isset($data['current']['wind_speed_10m'], $data['current']['wind_direction_10m'])) {
            $c = $data['current'];
            $current = [
                ...$this->wind($c['wind_speed_10m'], $c['wind_direction_10m'], $c['wind_gusts_10m'] ?? null),
                'temperature' => isset($c['temperature_2m']) ? round($c['temperature_2m'], 1) : null,
                'sunrise' => $data['daily']['sunrise'][0] ?? null,
            ];
        }

        $days = [];
        $hourly = $data['hourly'] ?? [];

        foreach ($hourly['time'] ?? [] as $i => $time) {
            if (! str_ends_with($time, 'T'.self::FORECAST_HOUR)) {
                continue;
            }

            $speed = $hourly['wind_speed_10m'][$i] ?? null;
            $direction = $hourly['wind_direction_10m'][$i] ?? null;

            if ($speed === null || $direction === null) {
                continue;
            }

            $days[substr($time, 0, 10)] = $this->wind($speed, $direction, $hourly['wind_gusts_10m'][$i] ?? null);
        }

        return ['current' => $current, 'days' => $days];
    }

    private function wind(float $speedKmh, float $direction, ?float $gustKmh): array
    {
        return [
            'kmh' => (int) round($speedKmh),
            'gust_kmh' => $gustKmh !== null ? (int) round($gustKmh) : null,
            'degrees' => (int) round($direction),
            'direction' => $this->directionText($direction),
            'beaufort' => $this->beaufort($speedKmh / 3.6),
            'effect' => $this->beachEffect($speedKmh, $direction),
        ];
    }

    /**
     * Wat de wind op het strand doet. Windrichting is waar hij vandaan komt,
     * dus wind uit het noorden is tegenwind op weg naar Bergen.
     */
    private function beachEffect(float $speedKmh, float $direction): ?string
    {
        if ($speedKmh < 12) {
            return null;
        }

        $angle = abs(fmod($direction - self::BEACH_HEADING + 540, 360) - 180);

        return match (true) {
            $angle <= 45 => 'tegenwind',
            $angle >= 135 => 'rugwind',
            default => 'zijwind',
        };
    }

    private function directionText(float $degrees): string
    {
        $directions = [
            'N', 'NNO', 'NO', 'ONO',
            'O', 'OZO', 'ZO', 'ZZO',
            'Z', 'ZZW', 'ZW', 'WZW',
            'W', 'WNW', 'NW', 'NNW',
        ];

        return $directions[(int) round($degrees / 22.5) % 16];
    }

    private function beaufort(float $speedMs): int
    {
        $limits = [0.3, 1.6, 3.4, 5.5, 8.0, 10.8, 13.9, 17.2, 20.8, 24.5, 28.5, 32.7];

        foreach ($limits as $bft => $limit) {
            if ($speedMs < $limit) {
                return $bft;
            }
        }

        return 12;
    }
}
