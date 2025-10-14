<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class WindDataService
{
    private const API_URL = 'https://api.windy.com/api/point-forecast/v2';
    
    // Egmond aan Zee coördinaten: 52°37'02.6"N 4°37'04.7"E
    private const EGMOND_LAT = 52.617389;
    private const EGMOND_LON = 4.617972;
    
    private string $apiKey;

    public function __construct()
    {
        $this->apiKey = env('WINDY_API_KEY', '');
    }

    /**
     * Haal actuele windgegevens op voor Egmond aan Zee
     */
    public function getCurrentWindData(): ?array
    {
        if (empty($this->apiKey)) {
            Log::warning('Windy API key niet geconfigureerd');
            return null;
        }

        // Cache voor 15 minuten
        return Cache::remember('wind_data_egmond', 900, function () {
            try {
                $response = Http::timeout(10)->post(self::API_URL, [
                    'lat' => self::EGMOND_LAT,
                    'lon' => self::EGMOND_LON,
                    'model' => 'gfs',
                    'parameters' => ['wind', 'windGust'],
                    'levels' => ['surface'],
                    'key' => $this->apiKey,
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    return $this->processWindData($data);
                }

                Log::error('Windy API error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return null;
            } catch (\Exception $e) {
                Log::error('Error fetching wind data', [
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                return null;
            }
        });
    }

    /**
     * Verwerk de ruwe windgegevens naar een bruikbaar formaat
     */
    private function processWindData(array $data): ?array
    {
        if (!isset($data['ts']) || empty($data['ts'])) {
            return null;
        }

        // Neem de eerste (meest actuele) waarde
        $index = 0;
        
        $windU = $data['wind_u-surface'][$index] ?? null;
        $windV = $data['wind_v-surface'][$index] ?? null;
        $gust = $data['gust-surface'][$index] ?? null;

        if ($windU === null || $windV === null) {
            return null;
        }

        // Bereken windsnelheid en richting
        $windSpeed = sqrt($windU * $windU + $windV * $windV);
        $windDirection = $this->calculateWindDirection($windU, $windV);
        $windDirectionText = $this->getWindDirectionText($windDirection);
        
        // Converteer m/s naar km/h en Beaufort
        $windSpeedKmh = round($windSpeed * 3.6, 1);
        $beaufort = $this->calculateBeaufort($windSpeed);

        return [
            'speed_ms' => round($windSpeed, 1),
            'speed_kmh' => $windSpeedKmh,
            'direction_degrees' => round($windDirection),
            'direction_text' => $windDirectionText,
            'beaufort' => $beaufort,
            'gust_ms' => $gust ? round($gust, 1) : null,
            'gust_kmh' => $gust ? round($gust * 3.6, 1) : null,
            'timestamp' => $data['ts'][$index],
            'location' => 'Egmond aan Zee',
        ];
    }

    /**
     * Bereken windrichting in graden (0 = Noord, 90 = Oost, etc.)
     * De windrichting geeft aan waar de wind VANDAAN komt
     */
    private function calculateWindDirection(float $u, float $v): float
    {
        // Bereken de hoek in radialen
        $angle = atan2(-$u, -$v);
        
        // Converteer naar graden (0-360)
        $degrees = rad2deg($angle);
        
        // Zorg ervoor dat het tussen 0 en 360 is
        if ($degrees < 0) {
            $degrees += 360;
        }
        
        return $degrees;
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

