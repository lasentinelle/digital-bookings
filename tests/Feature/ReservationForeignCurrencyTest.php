<?php

use App\ForeignCurrency;
use App\Models\Client;
use App\Models\Placement;
use App\Models\Reservation;
use App\Models\User;
use App\ReservationStatus;
use App\ReservationType;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->admin()->create();
    $this->actingAs($this->user);
});

it('stores a reservation with foreign currency amount and code', function () {
    $client = Client::factory()->create();
    $placement = Placement::factory()->create();

    $response = $this->post(route('reservations.store'), [
        'client_id' => $client->id,
        'product' => 'FX reservation',
        'placement_id' => $placement->id,
        'type' => ReservationType::Standard->value,
        'channel' => 'Run of site',
        'scope' => 'Mauritius only',
        'dates_booked' => json_encode(['2026-05-01']),
        'gross_amount' => 1000,
        'total_amount_to_pay' => 1150,
        'vat_exempt' => '0',
        'is_foreign_currency' => '1',
        'foreign_currency_amount' => 25.50,
        'foreign_currency_code' => ForeignCurrency::EUR->value,
        'status' => ReservationStatus::Option->value,
    ]);

    $response->assertRedirect(route('reservations.index'));

    $reservation = Reservation::where('product', 'FX reservation')->firstOrFail();
    expect($reservation->is_foreign_currency)->toBeTrue();
    expect((float) $reservation->foreign_currency_amount)->toBe(25.50);
    expect($reservation->foreign_currency_code)->toBe(ForeignCurrency::EUR);
});

it('requires foreign_currency_amount when is_foreign_currency is true', function () {
    $client = Client::factory()->create();
    $placement = Placement::factory()->create();

    $response = $this->post(route('reservations.store'), [
        'client_id' => $client->id,
        'product' => 'FX missing amount',
        'placement_id' => $placement->id,
        'type' => ReservationType::Standard->value,
        'channel' => 'Run of site',
        'scope' => 'Mauritius only',
        'dates_booked' => json_encode(['2026-05-01']),
        'gross_amount' => 1000,
        'total_amount_to_pay' => 1150,
        'vat_exempt' => '0',
        'is_foreign_currency' => '1',
        'foreign_currency_code' => ForeignCurrency::USD->value,
        'status' => ReservationStatus::Option->value,
    ]);

    $response->assertSessionHasErrors('foreign_currency_amount');
});

it('requires foreign_currency_code when is_foreign_currency is true', function () {
    $client = Client::factory()->create();
    $placement = Placement::factory()->create();

    $response = $this->post(route('reservations.store'), [
        'client_id' => $client->id,
        'product' => 'FX missing code',
        'placement_id' => $placement->id,
        'type' => ReservationType::Standard->value,
        'channel' => 'Run of site',
        'scope' => 'Mauritius only',
        'dates_booked' => json_encode(['2026-05-01']),
        'gross_amount' => 1000,
        'total_amount_to_pay' => 1150,
        'vat_exempt' => '0',
        'is_foreign_currency' => '1',
        'foreign_currency_amount' => 100,
        'status' => ReservationStatus::Option->value,
    ]);

    $response->assertSessionHasErrors('foreign_currency_code');
});

it('nullifies foreign_currency fields when is_foreign_currency is not set', function () {
    $client = Client::factory()->create();
    $placement = Placement::factory()->create();

    $response = $this->post(route('reservations.store'), [
        'client_id' => $client->id,
        'product' => 'No FX reservation',
        'placement_id' => $placement->id,
        'type' => ReservationType::Standard->value,
        'channel' => 'Run of site',
        'scope' => 'Mauritius only',
        'dates_booked' => json_encode(['2026-05-01']),
        'gross_amount' => 1000,
        'total_amount_to_pay' => 1150,
        'vat_exempt' => '0',
        'foreign_currency_amount' => 999,
        'foreign_currency_code' => ForeignCurrency::USD->value,
        'status' => ReservationStatus::Option->value,
    ]);

    $response->assertRedirect(route('reservations.index'));

    $reservation = Reservation::where('product', 'No FX reservation')->firstOrFail();
    expect($reservation->is_foreign_currency)->toBeFalse();
    expect($reservation->foreign_currency_amount)->toBeNull();
    expect($reservation->foreign_currency_code)->toBeNull();
});

it('shows the foreign currency amount on the show page', function () {
    $reservation = Reservation::factory()->create([
        'is_foreign_currency' => true,
        'foreign_currency_amount' => 123.45,
        'foreign_currency_code' => ForeignCurrency::USD,
    ]);

    $this->get(route('reservations.show', $reservation))
        ->assertOk()
        ->assertSee('USD')
        ->assertSee('123.45');
});
