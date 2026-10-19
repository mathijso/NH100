<?php

use App\Http\Controllers\CalendarFeedController;
use App\Services\RoutePlannerService;
use App\Services\TideDataService;
use App\Services\WindDataService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Route;

Route::get('/', function (RoutePlannerService $planner, WindDataService $wind) {
    $weather = $wind->getWeather();
    $range = $planner->dataRange();
    $today = CarbonImmutable::now(TideDataService::TIMEZONE)->startOfDay();

    // Vanaf vorige maand, zodat je nog even terug kunt kijken, tot het einde van de data
    $days = $range
        ? $planner->days(max($range[0], $today->subMonthNoOverflow()->startOfMonth()), $range[1], $weather['days'])
        : [];

    return view('welcome', [
        'days' => $days,
        'today' => $today->format('Y-m-d'),
        'currentWeather' => $weather['current'],
    ]);
});

Route::get('/agenda.ics', CalendarFeedController::class);
