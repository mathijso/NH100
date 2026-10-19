<?php

namespace App\Console\Commands;

use Carbon\CarbonImmutable;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

#[Signature('tides:fetch {year : Jaar om op te halen, bijv. 2027} {--path= : Map voor het CSV-bestand (standaard public/data)}')]
#[Description('Haal de astronomische hoog- en laagwatertijden van IJmuiden buitenhaven op bij Rijkswaterstaat')]
class FetchTideData extends Command
{
    private const API_URL = 'https://ddapi20-waterwebservices.rijkswaterstaat.nl/ONLINEWAARNEMINGENSERVICES/OphalenWaarnemingen';

    private const LOCATION = 'ijmuiden.buitenhaven';

    private const TIMEZONE = 'Europe/Amsterdam';

    public function handle(): int
    {
        $year = (int) $this->argument('year');
        $path = $this->option('path') ?: public_path('data');

        // Een dag marge aan beide kanten, zodat ook de eerste en laatste extremen
        // van het jaar buren hebben om HW/LW aan te bepalen.
        $response = Http::timeout(30)->post(self::API_URL, [
            'Locatie' => ['Code' => self::LOCATION],
            'AquoPlusWaarnemingMetadata' => [
                'AquoMetadata' => [
                    'Compartiment' => ['Code' => 'OW'],
                    'Grootheid' => ['Code' => 'WATHTE'],
                    'Groepering' => ['Code' => 'GETETBRKD2'],
                    'ProcesType' => 'astronomisch',
                ],
            ],
            'Periode' => [
                'Begindatumtijd' => ($year - 1).'-12-31T00:00:00.000+01:00',
                'Einddatumtijd' => ($year + 1).'-01-01T23:59:00.000+01:00',
            ],
        ]);

        $measurements = $response->json('WaarnemingenLijst.0.MetingenLijst');

        if ($response->failed() || ! $response->json('Succesvol') || empty($measurements)) {
            $this->error("Geen getijdendata ontvangen voor {$year} (HTTP {$response->status()}).");

            return self::FAILURE;
        }

        $rows = $this->toRows($measurements, $year, $skipped);

        if ($skipped > 0) {
            $this->warn("{$skipped} extremen overgeslagen omdat ze even hoog waren als een buur (geen HW/LW te bepalen).");
        }

        if (count($rows) < 1300) {
            $this->error('Onverwacht weinig getijden ('.count($rows)."), {$year} is waarschijnlijk nog niet (volledig) gepubliceerd.");

            return self::FAILURE;
        }

        if (! is_dir($path)) {
            mkdir($path, 0755, true);
        }

        $file = "{$path}/{$year}.csv";
        file_put_contents($file, implode("\n", [
            'Datum;Nederlandse tijd;Hoogwater/laagwater;Waarde',
            ...$rows,
        ])."\n");

        $this->info(count($rows)." getijden geschreven naar {$file}");

        return self::SUCCESS;
    }

    /**
     * De API geeft alleen tijdstip en hoogte van elk extreem. Hoog- en laagwater
     * wisselen elkaar af, dus een extreem hoger dan beide buren is HW, lager is LW.
     *
     * @return list<string>
     */
    private function toRows(array $measurements, int $year, ?int &$skipped = 0): array
    {
        $skipped = 0;
        $points = collect($measurements)
            ->map(fn (array $m) => [
                'time' => CarbonImmutable::parse($m['Tijdstip'])->setTimezone(self::TIMEZONE),
                'value' => (int) round($m['Meetwaarde']['Waarde_Numeriek']),
            ])
            ->sortBy(fn (array $p) => $p['time']->getTimestamp())
            ->values();

        $rows = [];

        foreach ($points as $i => $point) {
            if ($point['time']->year !== $year) {
                continue;
            }

            $neighbours = array_filter([$points[$i - 1] ?? null, $points[$i + 1] ?? null]);
            $neighbourValues = array_map(fn (array $p) => $p['value'], $neighbours);

            $type = match (true) {
                $point['value'] > max($neighbourValues) => 'HW',
                $point['value'] < min($neighbourValues) => 'LW',
                default => null,
            };

            if ($type === null) {
                $skipped++;

                continue;
            }

            $rows[] = sprintf(
                '%s;%s;%s;%d cm',
                $point['time']->format('d/m/Y'),
                $point['time']->format('H:i'),
                $type,
                $point['value'],
            );
        }

        // Nieuwste eerst, net als de export van Rijkswaterstaat
        return array_reverse($rows);
    }
}
