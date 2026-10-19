<?php

use App\Services\TideDataService;
use App\Services\WindDataService;
use Illuminate\Support\Facades\Route;

Route::get('/', function (TideDataService $tides, WindDataService $wind) {
    return view('welcome', [
        'tides' => $tides->getAllTides(),
        'windData' => $wind->getCurrentWindData(),
    ]);
});
