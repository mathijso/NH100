<?php

use Carbon\CarbonImmutable;

beforeEach(fn () => CarbonImmutable::setTestNow(CarbonImmutable::create(2026, 10, 19, 9, 0, 0, 'Europe/Amsterdam')));
afterEach(fn () => CarbonImmutable::setTestNow());

it('serves green weekend days as all-day events', function () {
    $response = $this->get('/agenda.ics');

    $response->assertOk()->assertHeader('Content-Type', 'text/calendar; charset=utf-8');

    $body = $response->getContent();
    $lines = explode("\r\n", $body);

    expect($body)->toStartWith("BEGIN:VCALENDAR\r\n")
        ->toEndWith("END:VCALENDAR\r\n")
        // za 24 okt 2026: eb 11:40, groen
        ->toContain("DTSTART;VALUE=DATE:20261024\r\n")
        ->toContain('UID:nh100-20261024@mathijsoggel.nl')
        // do 22 okt 2026 is groen maar geen weekend
        ->not->toContain('DTSTART;VALUE=DATE:20261022')
        // zomer is altijd rood
        ->not->toContain('DTSTART;VALUE=DATE:202707');

    foreach ($lines as $line) {
        expect(strlen($line))->toBeLessThanOrEqual(75);
    }
});

it('includes weekdays with dagen=alle', function () {
    expect($this->get('/agenda.ics?dagen=alle')->getContent())
        ->toContain('DTSTART;VALUE=DATE:20261022')
        ->toContain('X-WR-CALNAME:NH100 berijdbaar');
});
