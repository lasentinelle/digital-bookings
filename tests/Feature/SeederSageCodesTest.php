<?php

use App\Models\Client;
use App\Models\Salesperson;
use Database\Seeders\SalespersonSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('seeds all salespeople with a non-null sage_salesperson_code', function () {
    $this->seed(SalespersonSeeder::class);

    $salespeople = Salesperson::all();

    expect($salespeople)->toHaveCount(4);

    foreach ($salespeople as $salesperson) {
        expect($salesperson->sage_salesperson_code)->not->toBeNull();
    }
});

it('seeds each salesperson with the expected sage code', function () {
    $this->seed(SalespersonSeeder::class);

    $expected = [
        'patricia.caprice@lasentinelle.mu' => 'PATR',
        'jenna.moutou@lasentinelle.mu' => 'JENN',
        'gino.sophie@lasentinelle.mu' => 'GINO',
        'rachel.dauhoo@lasentinelle.mu' => 'RACH',
    ];

    foreach ($expected as $email => $code) {
        $salesperson = Salesperson::where('email', $email)->firstOrFail();

        expect($salesperson->sage_salesperson_code)->toBe($code);
    }
});

it('creates a client via factory with a sage_client_code matching the ART-#### pattern', function () {
    $client = Client::factory()->create();

    expect($client->sage_client_code)->toMatch('/^ART-\d{4}$/');
});
