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

it('stores a reservation with represented_client_id', function () {
    $billingClient = Client::factory()->create();
    $representedClient = Client::factory()->create();
    $placement = Placement::factory()->create();

    $response = $this->post(route('reservations.store'), [
        'client_id' => $billingClient->id,
        'represented_client_id' => $representedClient->id,
        'product' => 'Represented brand campaign',
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

    $reservation = Reservation::where('product', 'Represented brand campaign')->firstOrFail();
    expect($reservation->client_id)->toBe($billingClient->id);
    expect($reservation->represented_client_id)->toBe($representedClient->id);
});

it('rejects represented_client_id equal to client_id', function () {
    $client = Client::factory()->create();
    $placement = Placement::factory()->create();

    $response = $this->post(route('reservations.store'), [
        'client_id' => $client->id,
        'represented_client_id' => $client->id,
        'product' => 'Self represented',
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

    $response->assertSessionHasErrors('represented_client_id');
    expect(Reservation::where('product', 'Self represented')->exists())->toBeFalse();
});
