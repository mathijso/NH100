<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class MareaApiService
{
    private string $baseUrl = 'https://api.marea.ooo/v2';
    private string $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.marea.api_key');
        
        if (empty($this->apiKey)) {
            throw new \Exception('Marea API key not configured. Please set MAREA_API_KEY in your .env file.');
        }
    }

    /**
     * Get tide predictions for a location
     *
     * @param float $latitude
     * @param float $longitude
     * @param int $duration Duration in minutes (default: 35 days)
     * @param int $interval Interval between measurements in minutes (default: 60)
     * @param int|null $timestamp Start timestamp (default: now)
     * @return array|null
     */
    public function getTides(
        float $latitude,
        float $longitude,
        int $duration = 50400, // 35 days in minutes
        int $interval = 60,
        ?int $timestamp = null
    ): ?array {
        $timestamp = $timestamp ?? time();
        
        // Create a cache key based on the parameters (rounded to nearest hour for efficiency)
        $hourlyTimestamp = floor($timestamp / 3600) * 3600;
        $cacheKey = "marea_tides_{$latitude}_{$longitude}_{$hourlyTimestamp}_{$duration}_{$interval}";
        
        // Cache for 6 hours since tide predictions don't change frequently
        return Cache::remember($cacheKey, now()->addHours(6), function () use ($latitude, $longitude, $duration, $interval, $timestamp) {
            try {
                $response = Http::timeout(30)
                    ->withHeaders([
                        'x-marea-api-token' => $this->apiKey,
                        'Accept' => 'application/json',
                    ])
                    ->get("{$this->baseUrl}/tides", [
                        'latitude' => $latitude,
                        'longitude' => $longitude,
                        'duration' => $duration,
                        'interval' => $interval,
                        'timestamp' => $timestamp,
                        'datum' => 'MSL', // Mean Sea Level
                        'model' => 'FES2014', // Default model
                        'station_radius' => 50, // Prioritize station data within 50km
                    ]);

                if ($response->successful()) {
                    $data = $response->json();
                    
                    Log::info('Marea API call successful', [
                        'latitude' => $latitude,
                        'longitude' => $longitude,
                        'source' => $data['source'] ?? 'unknown',
                        'extremes_count' => count($data['extremes'] ?? []),
                    ]);
                    
                    return $data;
                }

                Log::error('Marea API call failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return null;
            } catch (\Exception $e) {
                Log::error('Marea API exception', [
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                
                return null;
            }
        });
    }

    /**
     * Get tide extremes (high and low tides) for Egmond aan Zee
     * Uses Petten Zuid station (closest to Egmond aan Zee)
     *
     * @param int $days Number of days to fetch (default: 35)
     * @return array|null
     */
    public function getEgmondTides(int $days = 35): ?array
    {
        $duration = $days * 24 * 60; // Convert days to minutes
        
        // Try to use Petten Zuid station first (closest to Egmond aan Zee)
        $stationId = config('services.marea.station_id', 'GESLA3:eb1c86d10b');
        
        $data = $this->getTidesByStation($stationId, $duration, 60);
        
        if (!$data) {
            // Fallback to coordinates if station fails
            Log::warning('Petten Zuid station failed, falling back to coordinates');
            $latitude = 52.618694;
            $longitude = 4.618250;
            $data = $this->getTides($latitude, $longitude, $duration, 60);
        }
        
        if (!$data) {
            return null;
        }
        
        // Transform API response to our expected format
        return $this->transformToNH100Format($data);
    }

    /**
     * Get tide predictions for a specific station
     *
     * @param string $stationId Station ID (e.g., 'GESLA3:eb1c86d10b')
     * @param int $duration Duration in minutes (default: 35 days)
     * @param int $interval Interval between measurements in minutes (default: 60)
     * @param int|null $timestamp Start timestamp (default: now)
     * @return array|null
     */
    public function getTidesByStation(
        string $stationId,
        int $duration = 50400,
        int $interval = 60,
        ?int $timestamp = null
    ): ?array {
        $timestamp = $timestamp ?? time();
        
        // Create a cache key based on the parameters
        $hourlyTimestamp = floor($timestamp / 3600) * 3600;
        $cacheKey = "marea_tides_station_{$stationId}_{$hourlyTimestamp}_{$duration}_{$interval}";
        
        // Cache for 6 hours
        return Cache::remember($cacheKey, now()->addHours(6), function () use ($stationId, $duration, $interval, $timestamp) {
            try {
                $response = Http::timeout(30)
                    ->withHeaders([
                        'x-marea-api-token' => $this->apiKey,
                        'Accept' => 'application/json',
                    ])
                    ->get("{$this->baseUrl}/tides", [
                        'station_id' => $stationId,
                        'duration' => $duration,
                        'interval' => $interval,
                        'timestamp' => $timestamp,
                        'datum' => 'MSL', // Mean Sea Level
                    ]);

                if ($response->successful()) {
                    $data = $response->json();
                    
                    Log::info('Marea API station call successful', [
                        'station_id' => $stationId,
                        'source' => $data['source'] ?? 'unknown',
                        'extremes_count' => count($data['extremes'] ?? []),
                    ]);
                    
                    return $data;
                }

                Log::error('Marea API station call failed', [
                    'station_id' => $stationId,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return null;
            } catch (\Exception $e) {
                Log::error('Marea API station exception', [
                    'station_id' => $stationId,
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                
                return null;
            }
        });
    }

    /**
     * Transform Marea API response to NH100 format
     *
     * @param array $apiData
     * @return array
     */
    private function transformToNH100Format(array $apiData): array
    {
        $tides = [];
        
        // Process extremes (high and low tides)
        foreach ($apiData['extremes'] ?? [] as $extreme) {
            $tides[] = [
                'time' => $extreme['datetime'],
                'type' => $extreme['state'] === 'HIGH TIDE' ? 'High' : 'Low',
                'height' => $extreme['height'],
            ];
        }
        
        // Sort by time
        usort($tides, function ($a, $b) {
            return strtotime($a['time']) - strtotime($b['time']);
        });
        
        return [
            'tides' => $tides,
            'source' => $apiData['source'] ?? 'UNKNOWN',
            'location' => [
                'latitude' => $apiData['latitude'] ?? null,
                'longitude' => $apiData['longitude'] ?? null,
                'origin' => $apiData['origin'] ?? null,
            ],
            'datum' => $apiData['datum'] ?? 'MSL',
            'unit' => $apiData['unit'] ?? 'm',
            'copyright' => $apiData['copyright'] ?? '',
        ];
    }

    /**
     * Get API usage information from response headers
     *
     * @return array|null
     */
    public function getApiUsage(): ?array
    {
        // This would need to be implemented with middleware to capture headers
        // from the last request. For now, return null.
        return null;
    }
}

