<?php

use App\Models\Client;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;

uses(RefreshDatabase::class);

it('renders the client-combobox component with dispatch-event and selected id', function () {
    $clients = Client::factory()->count(2)->create();
    $selected = $clients->first();

    $rendered = Blade::render(
        '<x-client-combobox name="client_id" :clients="$clients" :selected="$selected" dispatch-event="client-selected" required />',
        [
            'clients' => $clients,
            'selected' => $selected->id,
        ]
    );

    expect($rendered)->toContain('name="client_id"');
    expect($rendered)->toContain('combobox(');
    expect($rendered)->toContain('client-selected');
    expect($rendered)->toContain('selectedId: '.$selected->id);
    expect($rendered)->toContain($clients->first()->company_name);
    expect($rendered)->toContain($clients->last()->company_name);
    expect($rendered)->toContain('required');
});

it('renders the client-combobox without a selected id by default', function () {
    $clients = Client::factory()->count(2)->create();

    $rendered = Blade::render(
        '<x-client-combobox name="represented_client_id" :clients="$clients" />',
        ['clients' => $clients]
    );

    expect($rendered)->toContain('name="represented_client_id"');
    expect($rendered)->toContain('selectedId: null');
    expect($rendered)->not->toContain('required />');
});
