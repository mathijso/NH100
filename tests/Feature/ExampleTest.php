<?php

use Illuminate\Support\Facades\Http;

test('the application returns a successful response', function () {
    Http::fake(['*' => Http::response([], 500)]);

    $this->get('/')
        ->assertStatus(200)
        ->assertSee('NH100 Route Planner')
        ->assertSee('"date":"2027-12-31"', false)
        ->assertSee('og:image', false)
        ->assertDontSee('livewire', false);
});
