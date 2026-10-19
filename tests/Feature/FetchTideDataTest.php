<?php

use Illuminate\Support\Facades\Http;

function rwsResponse(array $extremes): array
{
    return [
        'Succesvol' => true,
        'WaarnemingenLijst' => [[
            'MetingenLijst' => array_map(fn ($e) => [
                'Tijdstip' => $e[0],
                'Meetwaarde' => ['Waarde_Numeriek' => $e[1]],
            ], $extremes),
        ]],
    ];
}

it('writes HW and LW rows for the requested year only', function () {
    // 1300+ extremen nodig om door de volledigheidscheck te komen
    $extremes = [['2026-12-31T21:51:00.000+01:00', 101]];
    $time = new DateTimeImmutable('2027-01-01T03:00:00+01:00');
    for ($i = 0; $i < 1400; $i++) {
        $extremes[] = [$time->format('Y-m-d\TH:i:s.000P'), $i % 2 === 0 ? -50 : 90];
        $time = $time->modify('+6 hours 12 minutes');
    }

    Http::fake(['*' => Http::response(rwsResponse($extremes))]);

    $dir = sys_get_temp_dir().'/nh100-tides-'.uniqid();

    $this->artisan('tides:fetch', ['year' => 2027, '--path' => $dir])->assertSuccessful();

    $lines = file("{$dir}/2027.csv", FILE_IGNORE_NEW_LINES);

    expect($lines[0])->toBe('Datum;Nederlandse tijd;Hoogwater/laagwater;Waarde')
        ->and(end($lines))->toBe('01/01/2027;03:00;LW;-50 cm')
        ->and($lines[count($lines) - 2])->toBe('01/01/2027;09:12;HW;90 cm')
        ->and(collect($lines)->contains(fn ($l) => str_contains($l, '/2026')))->toBeFalse();
});

it('fails when the year is not published yet', function () {
    Http::fake(['*' => Http::response(rwsResponse([
        ['2028-01-01T03:00:00.000+01:00', -50],
        ['2028-01-01T09:12:00.000+01:00', 90],
    ]))]);

    $dir = sys_get_temp_dir().'/nh100-tides-'.uniqid();

    $this->artisan('tides:fetch', ['year' => 2028, '--path' => $dir])->assertFailed();

    expect(file_exists("{$dir}/2028.csv"))->toBeFalse();
});
