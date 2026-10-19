<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class TideDataService
{
    private string $dataPath;

    public function __construct()
    {
        $this->dataPath = public_path('data');
    }

    /**
     * Alle getijden uit alle jaarbestanden, chronologisch.
     *
     * Bevat alleen wat de frontend gebruikt (tijd en HW/LW), de pagina
     * krijgt deze lijst inline mee en die moet klein blijven.
     *
     * @return list<array{time: string, type: 'High'|'Low'}>
     */
    public function getAllTides(): array
    {
        $tides = [];

        foreach ($this->getAvailableYears() as $year) {
            array_push($tides, ...$this->parseCsvFile("{$this->dataPath}/{$year}.csv"));
        }

        usort($tides, fn ($a, $b) => strcmp($a['time'], $b['time']));

        return $tides;
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
                $dateTime = Carbon::createFromFormat('d/m/Y H:i', trim($row[0]).' '.trim($row[1]), 'Europe/Amsterdam');
            } catch (\Exception $e) {
                Log::warning('Could not parse tide row', ['row' => $row, 'error' => $e->getMessage()]);

                continue;
            }

            $tides[] = [
                // UTC, zodat strcmp-sortering klopt over zomer- en wintertijd heen
                'time' => $dateTime->utc()->format('Y-m-d\TH:i\Z'),
                'type' => $type === 'HW' ? 'High' : 'Low',
            ];
        }

        fclose($handle);

        return $tides;
    }
}
