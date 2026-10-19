<?php

namespace App\Services;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Log;

class TideDataService
{
    public const TIMEZONE = 'Europe/Amsterdam';

    private string $dataPath;

    public function __construct()
    {
        $this->dataPath = public_path('data');
    }

    /**
     * Alle getijden uit alle jaarbestanden, gegroepeerd per lokale datum (Y-m-d)
     * en per dag chronologisch.
     *
     * @return array<string, list<array{time: CarbonImmutable, type: 'High'|'Low', height_cm: int}>>
     */
    public function getTidesByDate(): array
    {
        $byDate = [];

        foreach ($this->getAvailableYears() as $year) {
            foreach ($this->parseCsvFile("{$this->dataPath}/{$year}.csv") as $tide) {
                $byDate[$tide['time']->format('Y-m-d')][] = $tide;
            }
        }

        foreach ($byDate as &$tides) {
            usort($tides, fn ($a, $b) => $a['time'] <=> $b['time']);
        }

        ksort($byDate);

        return $byDate;
    }

    /**
     * @return list<int>
     */
    public function getAvailableYears(): array
    {
        $years = [];

        foreach (glob("{$this->dataPath}/*.csv") ?: [] as $file) {
            $year = basename($file, '.csv');
            if (ctype_digit($year)) {
                $years[] = (int) $year;
            }
        }

        sort($years);

        return $years;
    }

    /**
     * Rijkswaterstaat-formaat: Datum;Nederlandse tijd;Hoogwater/laagwater;Waarde
     */
    private function parseCsvFile(string $csvFile): array
    {
        $handle = fopen($csvFile, 'r');

        if (! $handle) {
            Log::error("Could not open CSV file: {$csvFile}");

            return [];
        }

        $tides = [];

        // Header overslaan
        fgetcsv($handle, 1000, ';', '"', '');

        while (($row = fgetcsv($handle, 1000, ';', '"', '')) !== false) {
            if (count($row) < 4) {
                continue;
            }

            $type = trim($row[2]);
            if (! in_array($type, ['HW', 'LW'])) {
                continue;
            }

            try {
                $dateTime = CarbonImmutable::createFromFormat('!d/m/Y H:i', trim($row[0]).' '.trim($row[1]), self::TIMEZONE);
            } catch (\Exception $e) {
                Log::warning('Could not parse tide row', ['row' => $row, 'error' => $e->getMessage()]);

                continue;
            }

            $tides[] = [
                'time' => $dateTime,
                'type' => $type === 'HW' ? 'High' : 'Low',
                'height_cm' => (int) filter_var($row[3], FILTER_SANITIZE_NUMBER_INT),
            ];
        }

        fclose($handle);

        return $tides;
    }
}
