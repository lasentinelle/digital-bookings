<?php

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

it('stores a reservation with is_cash set to true', function () {
    $client = Client::factory()->create();
    $placement = Placement::factory()->create();

    $response = $this->post(route('reservations.store'), [
        'client_id' => $client->id,
        'product' => 'Cash reservation',
        'placement_id' => $placement->id,
        'type' => ReservationType::Standard->value,
        'channel' => 'Run of site',
        'scope' => 'Mauritius only',
        'dates_booked' => json_encode(['2026-05-01']),
        'gross_amount' => 1000,
        'total_amount_to_pay' => 1150,
        'vat_exempt' => '0',
        'is_cash' => '1',
        'status' => ReservationStatus::Option->value,
    ]);

    $response->assertRedirect(route('reservations.index'));

    $reservation = Reservation::where('product', 'Cash reservation')->firstOrFail();
    expect($reservation->is_cash)->toBeTrue();
});

it('defaults is_cash to false when not submitted', function () {
    $client = Client::factory()->create();
    $placement = Placement::factory()->create();

    $response = $this->post(route('reservations.store'), [
        'client_id' => $client->id,
        'product' => 'Non cash reservation',
        'placement_id' => $placement->id,
        'type' => ReservationType::Standard->value,
        'channel' => 'Run of site',
        'scope' => 'Mauritius only',
        'dates_booked' => json_encode(['2026-05-01']),
        'gross_amount' => 1000,
        'total_amount_to_pay' => 1150,
        'vat_exempt' => '0',
        'status' => ReservationStatus::Option->value,
    ]);

    $response->assertRedirect(route('reservations.index'));

    $reservation = Reservation::where('product', 'Non cash reservation')->firstOrFail();
    expect($reservation->is_cash)->toBeFalse();
});

it('displays the Cash badge on the show page when is_cash is true', function () {
    $reservation = Reservation::factory()->create(['is_cash' => true]);

    $this->get(route('reservations.show', $reservation))
        ->assertOk()
        ->assertSee('Cash');
});

it('does not show the Cash badge when is_cash is false', function () {
    $reservation = Reservation::factory()->create(['is_cash' => false]);

    $this->get(route('reservations.show', $reservation))
        ->assertOk()
        ->assertDontSee('bg-emerald-50', false);
});
