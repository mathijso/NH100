<?php

use Illuminate\Support\Facades\Http;

test('the application returns a successful response', function () {
    Http::fake(['*' => Http::response([], 500)]);

    $this->get('/')
        ->assertStatus(200)
        ->assertSee('NH100 Route Planner')
        ->assertSee('"date":"2027-12-31"', false)
        ->assertSee('og:image', false)
        // Geen Livewire-scripts op deze pagina. De bezoekersbeacon noemt het
        // event livewire:navigated wel, dus kijk naar de scripts zelf.
        ->assertDontSee('livewire.js', false)
        ->assertDontSee('livewire.min.js', false)
        ->assertSee('https://hub.oggel-codelabs.nl/api/v1/b', false);
});
