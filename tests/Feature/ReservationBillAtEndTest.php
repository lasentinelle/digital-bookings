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

it('stores a reservation with bill_at_end_of_campaign set to true', function () {
    $client = Client::factory()->create();
    $placement = Placement::factory()->create();

    $response = $this->post(route('reservations.store'), [
        'client_id' => $client->id,
        'product' => 'Bill-at-end reservation',
        'placement_id' => $placement->id,
        'type' => ReservationType::Standard->value,
        'channel' => 'Run of site',
        'scope' => 'Mauritius only',
        'dates_booked' => json_encode(['2026-05-01']),
        'gross_amount' => 1000,
        'total_amount_to_pay' => 1150,
        'vat_exempt' => '0',
        'bill_at_end_of_campaign' => '1',
        'status' => ReservationStatus::Option->value,
    ]);

    $response->assertRedirect(route('reservations.index'));

    $reservation = Reservation::where('product', 'Bill-at-end reservation')->firstOrFail();
    expect($reservation->bill_at_end_of_campaign)->toBeTrue();
});

it('defaults bill_at_end_of_campaign to false when not submitted', function () {
    $client = Client::factory()->create();
    $placement = Placement::factory()->create();

    $response = $this->post(route('reservations.store'), [
        'client_id' => $client->id,
        'product' => 'Immediate billing',
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

    $reservation = Reservation::where('product', 'Immediate billing')->firstOrFail();
    expect($reservation->bill_at_end_of_campaign)->toBeFalse();
});

it('displays the Bill at end badge on the show page when set', function () {
    $reservation = Reservation::factory()->create(['bill_at_end_of_campaign' => true]);

    $this->get(route('reservations.show', $reservation))
        ->assertOk()
        ->assertSee('Bill at end of campaign');
});
