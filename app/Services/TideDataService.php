<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class TideDataService
{
    private string $dataPath;

    public function __construct()
    {
        $this->dataPath = public_path('data');
    }

    /**
     * Get tide data for a specific year
     * 
     * @param int $year
     * @return array|null
     */
    public function getTidesByYear(int $year): ?array
    {
        $csvFile = "{$this->dataPath}/{$year}.csv";
        
        if (!file_exists($csvFile)) {
            Log::warning("Tide data file not found for year {$year}");
            return null;
        }

        return $this->parseCsvFile($csvFile);
    }

    /**
     * Get all available tide data (all years)
     * 
     * @return array
     */
    public function getAllTides(): array
    {
        $allTides = [];
        
        // Scan data directory for CSV files
        if (!is_dir($this->dataPath)) {
            Log::error("Data directory not found: {$this->dataPath}");
            return [];
        }

        $files = glob("{$this->dataPath}/*.csv");
        
        foreach ($files as $file) {
            $year = basename($file, '.csv');
            if (is_numeric($year)) {
                $tides = $this->parseCsvFile($file);
                if ($tides) {
                    $allTides = array_merge($allTides, $tides);
                }
            }
        }

        // Sort by datetime
        usort($allTides, function ($a, $b) {
            return strtotime($a['time']) - strtotime($b['time']);
        });

        return $allTides;
    }

    /**
     * Get tide data for a date range
     * 
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return array
     */
    public function getTidesForDateRange(Carbon $startDate, Carbon $endDate): array
    {
        $years = range($startDate->year, $endDate->year);
        $allTides = [];

        foreach ($years as $year) {
            $yearTides = $this->getTidesByYear($year);
            if ($yearTides) {
                $allTides = array_merge($allTides, $yearTides);
            }
        }

        // Filter by date range
        $filteredTides = array_filter($allTides, function ($tide) use ($startDate, $endDate) {
            $tideDate = Carbon::parse($tide['time']);
            return $tideDate->between($startDate, $endDate);
        });

        return array_values($filteredTides);
    }

    /**
     * Get tide data for the next X days
     * 
     * @param int $days
     * @return array
     */
    public function getTidesForNextDays(int $days = 90): array
    {
        $startDate = Carbon::now();
        $endDate = Carbon::now()->addDays($days);
        
        return $this->getTidesForDateRange($startDate, $endDate);
    }

    /**
     * Parse CSV file and return tide data
     * 
     * @param string $csvFile
     * @return array|null
     */
    private function parseCsvFile(string $csvFile): ?array
    {
        try {
            $tides = [];
            $handle = fopen($csvFile, 'r');
            
            if (!$handle) {
                Log::error("Could not open CSV file: {$csvFile}");
                return null;
            }

            // Skip header row
            $header = fgetcsv($handle, 1000, ';', '"', '');
            
            while (($row = fgetcsv($handle, 1000, ';', '"', '')) !== false) {
                // Parse row: Datum;Nederlandsetijd;Hoogwater/laagwater;Waarde
                if (count($row) < 4) {
                    continue;
                }

                $datum = trim($row[0]);
                $tijd = trim($row[1]);
                $type = trim($row[2]);
                $waarde = trim($row[3]);

                // Skip rows with invalid type (M, K, etc.)
                if (!in_array($type, ['HW', 'LW'])) {
                    continue;
                }

                // Parse date and time
                try {
                    // Date format: DD/MM/YYYY
                    // Time format: HH:MM
                    $dateTime = Carbon::createFromFormat('d/m/Y H:i', "{$datum} {$tijd}", 'Europe/Amsterdam');
                    
                    // Parse height value (remove " cm" and convert to meters)
                    $heightCm = (int) filter_var($waarde, FILTER_SANITIZE_NUMBER_INT);
                    $heightM = $heightCm / 100; // Convert cm to meters

                    $tides[] = [
                        'time' => $dateTime->toIso8601String(),
                        'type' => $type === 'HW' ? 'High' : 'Low',
                        'height' => $heightM,
                        'height_cm' => $heightCm,
                    ];
                } catch (\Exception $e) {
                    Log::warning("Could not parse tide row", [
                        'row' => $row,
                        'error' => $e->getMessage()
                    ]);
                    continue;
                }
            }

            fclose($handle);

            Log::info("Parsed tide data from CSV", [
                'file' => basename($csvFile),
                'count' => count($tides)
            ]);

            return $tides;
        } catch (\Exception $e) {
            Log::error("Error parsing CSV file", [
                'file' => $csvFile,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Get available years
     * 
     * @return array
     */
    public function getAvailableYears(): array
    {
        if (!is_dir($this->dataPath)) {
            return [];
        }

        $files = glob("{$this->dataPath}/*.csv");
        $years = [];

        foreach ($files as $file) {
            $year = basename($file, '.csv');
            if (is_numeric($year)) {
                $years[] = (int) $year;
            }
        }

        sort($years);
        return $years;
    }

    /**
     * Transform to NH100 format (for compatibility)
     * 
     * @param array $tides
     * @return array
     */
    public function transformToNH100Format(array $tides): array
    {
        return [
            'tides' => $tides,
            'source' => 'Rijkswaterstaat CSV',
            'location' => [
                'name' => 'Egmond aan Zee',
                'latitude' => 52.618694,
                'longitude' => 4.618250,
            ],
            'years' => $this->getAvailableYears(),
        ];
    }
}

