<?php

namespace App\Http\Controllers;

use App\Services\RoutePlannerService;
use App\Services\TideDataService;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Agenda-abonnement met de groene dagen, standaard alleen in het weekend.
 * /agenda.ics?dagen=alle geeft ook de doordeweekse dagen.
 */
class CalendarFeedController extends Controller
{
    public function __invoke(Request $request, RoutePlannerService $planner): Response
    {
        $allDays = $request->query('dagen') === 'alle';
        $range = $planner->dataRange();
        $now = CarbonImmutable::now(TideDataService::TIMEZONE);

        $lines = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//Oggel Codelabs//NH100 Route Planner//NL',
            'CALSCALE:GREGORIAN',
            'METHOD:PUBLISH',
            'X-WR-CALNAME:'.($allDays ? 'NH100 berijdbaar' : 'NH100 berijdbaar (weekend)'),
            'X-WR-TIMEZONE:'.TideDataService::TIMEZONE,
            'REFRESH-INTERVAL;VALUE=DURATION:PT12H',
            'X-PUBLISHED-TTL:PT12H',
        ];

        if ($range) {
            $from = max($range[0], $now->subDays(30)->startOfDay());

            foreach ($planner->days($from, $range[1]) as $day) {
                if ($day['status'] !== 'green' || (! $allDays && ! $day['weekend'])) {
                    continue;
                }

                $date = CarbonImmutable::parse($day['date']);
                $tides = implode(', ', array_map(fn ($t) => "{$t['type']} {$t['time']}", $day['tides']));
                $description = "{$day['reason']}.\\nGetijden: {$tides}.\\nZon op: {$day['sunrise']}. Strand tussen 10:30 en 12:00.\\n"
                    .url('/').'/';

                array_push($lines,
                    'BEGIN:VEVENT',
                    'UID:nh100-'.$date->format('Ymd').'@mathijsoggel.nl',
                    'DTSTAMP:'.$now->utc()->format('Ymd\THis\Z'),
                    'DTSTART;VALUE=DATE:'.$date->format('Ymd'),
                    'DTEND;VALUE=DATE:'.$date->addDay()->format('Ymd'),
                    'SUMMARY:'.($day['extra_low'] ? 'NH100 berijdbaar, extra laag water' : 'NH100 berijdbaar'),
                    'DESCRIPTION:'.$this->escape($description),
                    'URL:'.url('/').'/',
                    'TRANSP:TRANSPARENT',
                    'END:VEVENT',
                );
            }
        }

        $lines[] = 'END:VCALENDAR';

        $body = implode("\r\n", array_map(fn ($line) => $this->fold($line), $lines))."\r\n";

        return response($body, 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'inline; filename="nh100.ics"',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    private function escape(string $text): string
    {
        // \n is al als letterlijke escape in de tekst gezet
        return str_replace([',', ';'], ['\,', '\;'], $text);
    }

    /**
     * Regels langer dan 75 octets vouwen (RFC 5545), zonder UTF-8 tekens te breken.
     */
    private function fold(string $line): string
    {
        $out = '';
        $current = '';
        $limit = 75;

        foreach (mb_str_split($line) as $char) {
            if (strlen($current) + strlen($char) > $limit) {
                $out .= $current."\r\n ";
                $current = '';
                $limit = 74; // de spatie aan het begin telt mee
            }
            $current .= $char;
        }

        return $out.$current;
    }
}
