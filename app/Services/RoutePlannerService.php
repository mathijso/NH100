<?php

namespace App\Services;

use Carbon\CarbonImmutable;

/**
 * Beoordeelt per dag of de NH100 berijdbaar is. Dit is de enige plek met de
 * regels; pagina en agenda-feed krijgen allebei het resultaat van days().
 */
class RoutePlannerService
{
    // Laagwater op of onder deze stand valt bij de laagste ~30% van de dagelijkse eb's
    public const EXTRA_LOW_CM = -80;

    // Coördinaten Egmond aan Zee, voor zonsopkomst
    private const LAT = 52.6187;

    private const LON = 4.6182;

    private ?array $tidesByDate = null;

    public function __construct(private TideDataService $tides) {}

    /**
     * @param  array<string, array>  $windByDate  wind om 11:00 per datum (Y-m-d), zie WindDataService
     * @return list<array>
     */
    public function days(CarbonImmutable $from, CarbonImmutable $to, array $windByDate = []): array
    {
        $tidesByDate = $this->tidesByDate();
        $days = [];

        for ($date = $from->startOfDay(); $date <= $to; $date = $date->addDay()) {
            $key = $date->format('Y-m-d');
            $dayTides = $tidesByDate[$key] ?? [];
            $result = $this->evaluate($date, $dayTides);

            $days[] = [
                'date' => $key,
                'weekend' => $date->isWeekend(),
                'status' => $result['status'],
                'reason' => $result['reason'],
                'tides' => array_map(fn ($t) => [
                    'time' => $t['time']->format('H:i'),
                    'type' => $t['type'] === 'Low' ? 'Eb' : 'Vloed',
                    'cm' => $t['height_cm'],
                ], $dayTides),
                'low_cm' => $result['low']['height_cm'] ?? null,
                'extra_low' => isset($result['low']) && $result['status'] !== 'red'
                    && $result['low']['height_cm'] <= self::EXTRA_LOW_CM,
                'sunrise' => $this->sunrise($date),
                'wind' => $windByDate[$key] ?? null,
            ];
        }

        return $days;
    }

    /**
     * Eerste en laatste datum waarvoor getijdendata is.
     *
     * @return array{0: CarbonImmutable, 1: CarbonImmutable}|null
     */
    public function dataRange(): ?array
    {
        $dates = array_keys($this->tidesByDate());

        if (empty($dates)) {
            return null;
        }

        return [
            CarbonImmutable::parse(reset($dates), TideDataService::TIMEZONE),
            CarbonImmutable::parse(end($dates), TideDataService::TIMEZONE),
        ];
    }

    /**
     * @param  list<array{time: CarbonImmutable, type: string, height_cm: int}>  $tides  getijden van deze dag
     * @return array{status: 'green'|'amber'|'red', reason: string, low?: array}
     */
    public function evaluate(CarbonImmutable $date, array $tides): array
    {
        $date = $date->setTimezone(TideDataService::TIMEZONE)->startOfDay();
        $lows = array_values(array_filter($tides, fn ($t) => $t['type'] === 'Low'));
        $highs = array_values(array_filter($tides, fn ($t) => $t['type'] === 'High'));

        // 1 mei - 1 oktober: strand alleen voor 10:00 toegankelijk, wij rijden het later
        if ($date->month >= 5 && $date->month <= 9) {
            return ['status' => 'red', 'reason' => 'Strand voor 10:00 toegankelijk'];
        }

        // Regel 1: groen als er eb valt tussen 08:00 en 15:00
        $from = $date->setTime(8, 0);
        $until = $date->setTime(15, 0);
        foreach ($lows as $low) {
            if ($low['time'] >= $from && $low['time'] <= $until) {
                return [
                    'status' => 'green',
                    'reason' => 'Eb om '.$low['time']->format('H:i').' (tussen 08:00–15:00)',
                    'low' => $low,
                ];
            }
        }

        // Regel 2: rood als de laatste vloed vóór 10:30 minder dan 2 uur geleden is
        $windowStart = $date->setTime(10, 30);
        $highsBefore = array_filter($highs, fn ($h) => $h['time'] <= $windowStart);
        $lastHigh = end($highsBefore);
        if ($lastHigh && $lastHigh['time']->diffInMinutes($windowStart) < 120) {
            return ['status' => 'red', 'reason' => 'Te dicht op vloed'];
        }

        // Regel 3: overlap strandvenster 10:30-12:00 met eb-venster (4 uur voor / 2 uur na eb)
        $windowEnd = $date->setTime(12, 0);
        $bestMinutes = 0;
        $bestLow = null;
        foreach ($lows as $low) {
            $start = max($windowStart, $low['time']->subHours(4));
            $end = min($windowEnd, $low['time']->addHours(2));
            $minutes = max(0, ($end->getTimestamp() - $start->getTimestamp()) / 60);

            if ($minutes > $bestMinutes) {
                $bestMinutes = $minutes;
                $bestLow = $low;
            }
        }

        if ($bestMinutes >= 90) {
            return ['status' => 'green', 'reason' => 'Het is eb genoeg', 'low' => $bestLow];
        }

        if ($bestMinutes >= 30) {
            return ['status' => 'amber', 'reason' => 'Het kan misschien', 'low' => $bestLow];
        }

        if (! empty($lows)) {
            return ['status' => 'red', 'reason' => 'Het is te vloed'];
        }

        return ['status' => 'red', 'reason' => 'Geen data beschikbaar'];
    }

    private function sunrise(CarbonImmutable $date): ?string
    {
        $info = date_sun_info($date->setTime(12, 0)->getTimestamp(), self::LAT, self::LON);

        if (! is_int($info['sunrise'] ?? null)) {
            return null;
        }

        return CarbonImmutable::createFromTimestamp($info['sunrise'], TideDataService::TIMEZONE)->format('H:i');
    }

    private function tidesByDate(): array
    {
        return $this->tidesByDate ??= $this->tides->getTidesByDate();
    }
}
