<?php

use App\Services\RoutePlannerService;
use Carbon\CarbonImmutable;

/**
 * @param  list<array{0: 'Eb'|'Vloed', 1: string, 2?: int}>  $pairs
 */
function dayTides(string $date, array $pairs): array
{
    return array_map(fn ($p) => [
        'time' => CarbonImmutable::parse("{$date} {$p[1]}", 'Europe/Amsterdam'),
        'type' => $p[0] === 'Eb' ? 'Low' : 'High',
        'height_cm' => $p[2] ?? 0,
    ], $pairs);
}

function evaluateDay(string $date, array $pairs): array
{
    return app(RoutePlannerService::class)->evaluate(
        CarbonImmutable::parse($date, 'Europe/Amsterdam'),
        dayTides($date, $pairs),
    );
}

it('is green when the full beach window falls inside the eb window', function () {
    expect(evaluateDay('2025-10-13', [['Eb', '10:30']])['status'])->toBe('green');
});

it('is green when eb falls between 08:00 and 15:00', function (string $date, array $pairs) {
    expect(evaluateDay($date, $pairs)['status'])->toBe('green');
})->with([
    'eb 08:24' => ['2025-10-16', [['Vloed', '00:17'], ['Eb', '08:24'], ['Vloed', '12:50'], ['Eb', '20:16']]],
    'eb 10:00' => ['2025-10-20', [['Vloed', '01:40'], ['Eb', '10:00'], ['Vloed', '13:58'], ['Eb', '21:38']]],
    'eb 14:59' => ['2025-10-11', [['Eb', '02:36'], ['Vloed', '07:09'], ['Eb', '14:59'], ['Vloed', '19:26']]],
]);

it('is amber with 30 minutes overlap', function () {
    $result = evaluateDay('2025-11-04', [['Eb', '03:00'], ['Vloed', '07:30'], ['Eb', '15:30'], ['Vloed', '20:00']]);

    expect($result['status'])->toBe('amber')
        ->and($result['reason'])->toBe('Het kan misschien');
});

it('is red when vloed was less than 2 hours before 10:30', function () {
    $result = evaluateDay('2025-10-13', [['Eb', '04:15'], ['Vloed', '08:51'], ['Eb', '16:38'], ['Vloed', '21:08']]);

    expect($result)->toMatchArray(['status' => 'red', 'reason' => 'Te dicht op vloed']);
});

it('is red when there is no overlap', function (string $date, array $pairs) {
    expect(evaluateDay($date, $pairs)['status'])->toBe('red');
})->with([
    'eb 07:35' => ['2025-10-14', [['Eb', '07:35']]],
    'eb 15:47' => ['2025-10-12', [['Eb', '03:22'], ['Vloed', '07:56'], ['Eb', '15:47'], ['Vloed', '20:12']]],
]);

it('is red without tide data in winter', function () {
    expect(evaluateDay('2025-11-05', []))->toMatchArray(['status' => 'red', 'reason' => 'Geen data beschikbaar']);
});

it('is red in summer regardless of the tide', function () {
    expect(evaluateDay('2025-07-01', [['Eb', '10:30']])['status'])->toBe('red');
});

it('builds days with weekend, extra low water, sunrise and wind', function () {
    $planner = app(RoutePlannerService::class);
    $tz = 'Europe/Amsterdam';

    $days = $planner->days(
        CarbonImmutable::create(2026, 10, 24, 0, 0, 0, $tz),
        CarbonImmutable::create(2026, 10, 26, 0, 0, 0, $tz),
        ['2026-10-24' => ['kmh' => 20]],
    );

    expect($days)->toHaveCount(3)
        ->and($days[0])->toMatchArray([
            'date' => '2026-10-24',
            'weekend' => true,
            'status' => 'green',
            'reason' => 'Eb om 11:40 (tussen 08:00–15:00)',
            'wind' => ['kmh' => 20],
        ])
        ->and($days[0]['tides'][1])->toBe(['time' => '11:40', 'type' => 'Eb', 'cm' => $days[0]['low_cm']])
        ->and($days[0]['extra_low'])->toBe($days[0]['low_cm'] <= RoutePlannerService::EXTRA_LOW_CM)
        ->and($days[0]['sunrise'])->toMatch('/^08:\d\d$/')
        ->and($days[2]['weekend'])->toBeFalse()
        ->and($days[2]['wind'])->toBeNull();
});
