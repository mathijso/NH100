<?php

use App\Services\WindDataService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

it('takes the 11:00 forecast per day and labels the beach wind', function () {
    Cache::flush();
    Http::fake(['*' => Http::response([
        'current' => ['temperature_2m' => 11.26, 'wind_speed_10m' => 15.5, 'wind_direction_10m' => 95, 'wind_gusts_10m' => 27],
        'daily' => ['sunrise' => ['2026-10-19T08:14']],
        'hourly' => [
            'time' => ['2026-10-19T10:00', '2026-10-19T11:00', '2026-10-20T11:00', '2026-10-21T11:00', '2026-10-22T11:00'],
            'wind_speed_10m' => [5, 30, 30, 30, 8],
            'wind_direction_10m' => [0, 200, 10, 280, 0],
            'wind_gusts_10m' => [8, 45, 45, 45, 12],
        ],
    ])]);

    $weather = app(WindDataService::class)->getWeather();

    expect($weather['current'])->toMatchArray(['temperature' => 11.3, 'direction' => 'O', 'beaufort' => 3, 'sunrise' => '2026-10-19T08:14'])
        ->and(array_keys($weather['days']))->toBe(['2026-10-19', '2026-10-20', '2026-10-21', '2026-10-22'])
        ->and($weather['days']['2026-10-19'])->toMatchArray(['kmh' => 30, 'direction' => 'ZZW', 'beaufort' => 5, 'effect' => 'rugwind'])
        ->and($weather['days']['2026-10-20']['effect'])->toBe('tegenwind')
        ->and($weather['days']['2026-10-21']['effect'])->toBe('zijwind')
        ->and($weather['days']['2026-10-22']['effect'])->toBeNull();
});

it('returns empty weather when the API fails', function () {
    Cache::flush();
    Http::fake(['*' => Http::response('kapot', 500)]);

    expect(app(WindDataService::class)->getWeather())->toBe(['current' => null, 'days' => []]);
});
