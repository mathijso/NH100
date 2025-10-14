<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class WindDataService
{
    private const API_URL = 'https://api.open-meteo.com/v1/forecast';
    
    // Egmond aan Zee coördinaten: 52°37'02.6"N 4°37'04.7"E
    private const EGMOND_LAT = 52.624449;
    private const EGMOND_LON = 4.61718;

    /**
     * Haal actuele weergegevens op voor Egmond aan Zee
     */
    public function getCurrentWindData(): ?array
    {
        // Cache voor 15 minuten
        return Cache::remember('wind_data_egmond', 900, function () {
            try {
                $response = Http::timeout(10)->get(self::API_URL, [
                    'latitude' => self::EGMOND_LAT,
                    'longitude' => self::EGMOND_LON,
                    'daily' => 'sunrise',
                    'current' => 'temperature_2m,wind_speed_10m,wind_direction_10m,wind_gusts_10m',
                    'timezone' => 'auto',
                    'forecast_days' => 1,
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    return $this->processWindData($data);
                }

                Log::error('Open-Meteo API error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return null;
            } catch (\Exception $e) {
                Log::error('Error fetching weather data', [
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                return null;
            }
        });
    }

    /**
     * Verwerk de ruwe weergegevens naar een bruikbaar formaat
     */
    private function processWindData(array $data): ?array
    {
        if (!isset($data['current'])) {
            return null;
        }

        $current = $data['current'];
        $daily = $data['daily'] ?? [];
        
        $windSpeedKmh = $current['wind_speed_10m'] ?? null;
        $windDirection = $current['wind_direction_10m'] ?? null;
        $gustKmh = $current['wind_gusts_10m'] ?? null;
        $temperature = $current['temperature_2m'] ?? null;
        $sunrise = $daily['sunrise'][0] ?? null;

        if ($windSpeedKmh === null || $windDirection === null) {
            return null;
        }

        $windDirectionText = $this->getWindDirectionText($windDirection);
        
        // Converteer km/h naar m/s voor Beaufort berekening
        $windSpeedMs = $windSpeedKmh / 3.6;
        $beaufort = $this->calculateBeaufort($windSpeedMs);

        return [
            'speed_ms' => round($windSpeedMs, 1),
            'speed_kmh' => round($windSpeedKmh, 1),
            'direction_degrees' => round($windDirection),
            'direction_text' => $windDirectionText,
            'beaufort' => $beaufort,
            'gust_ms' => $gustKmh ? round($gustKmh / 3.6, 1) : null,
            'gust_kmh' => $gustKmh ? round($gustKmh, 1) : null,
            'temperature' => $temperature ? round($temperature, 1) : null,
            'sunrise' => $sunrise,
            'timestamp' => $current['time'] ?? null,
            'location' => 'Egmond aan Zee',
        ];
    }

    /**
     * Converteer graden naar windrichting tekst
     */
    private function getWindDirectionText(float $degrees): string
    {
        $directions = [
            'N', 'NNO', 'NO', 'ONO',
            'O', 'OZO', 'ZO', 'ZZO',
            'Z', 'ZZW', 'ZW', 'WZW',
            'W', 'WNW', 'NW', 'NNW'
        ];
        
        $index = round($degrees / 22.5) % 16;
        return $directions[$index];
    }

    /**
     * Bereken Beaufort schaal uit windsnelheid (m/s)
     */
    private function calculateBeaufort(float $speedMs): int
    {
        if ($speedMs < 0.3) return 0;
        if ($speedMs < 1.6) return 1;
        if ($speedMs < 3.4) return 2;
        if ($speedMs < 5.5) return 3;
        if ($speedMs < 8.0) return 4;
        if ($speedMs < 10.8) return 5;
        if ($speedMs < 13.9) return 6;
        if ($speedMs < 17.2) return 7;
        if ($speedMs < 20.8) return 8;
        if ($speedMs < 24.5) return 9;
        if ($speedMs < 28.5) return 10;
        if ($speedMs < 32.7) return 11;
        return 12;
    }
}

