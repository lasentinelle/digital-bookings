<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

it('removes the agencies table after the migration runs', function () {
    expect(Schema::hasTable('agencies'))->toBeFalse();
});

it('removes the agency_id column from the reservations table', function () {
    expect(Schema::hasColumn('reservations', 'agency_id'))->toBeFalse();
});

it('retains the represented_client_id column on reservations', function () {
    expect(Schema::hasColumn('reservations', 'represented_client_id'))->toBeTrue();
});
