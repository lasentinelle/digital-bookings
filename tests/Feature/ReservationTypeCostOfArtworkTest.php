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

it('stores a cost_of_artwork reservation and zeroes discount and commission', function () {
    $client = Client::factory()->create();
    $placement = Placement::factory()->create();

    $response = $this->post(route('reservations.store'), [
        'client_id' => $client->id,
        'product' => 'Artwork fee',
        'placement_id' => $placement->id,
        'type' => ReservationType::CostOfArtwork->value,
        'channel' => 'Run of site',
        'scope' => 'Mauritius only',
        'dates_booked' => json_encode(['2026-05-01']),
        'gross_amount' => 500,
        'total_amount_to_pay' => 575,
        'discount' => 100,
        'commission' => 50,
        'vat_exempt' => '0',
        'status' => ReservationStatus::Option->value,
    ]);

    $response->assertRedirect(route('reservations.index'));

    $reservation = Reservation::where('product', 'Artwork fee')->firstOrFail();
    expect($reservation->type)->toBe(ReservationType::CostOfArtwork);
    expect((float) $reservation->discount)->toBe(0.0);
    expect((float) $reservation->commission)->toBe(0.0);
});

it('stores a standard reservation with its discount and commission intact', function () {
    $client = Client::factory()->create();
    $placement = Placement::factory()->create();

    $response = $this->post(route('reservations.store'), [
        'client_id' => $client->id,
        'product' => 'Standard reservation',
        'placement_id' => $placement->id,
        'type' => ReservationType::Standard->value,
        'channel' => 'Run of site',
        'scope' => 'Mauritius only',
        'dates_booked' => json_encode(['2026-05-01']),
        'gross_amount' => 1000,
        'total_amount_to_pay' => 1150,
        'discount' => 100,
        'commission' => 50,
        'vat_exempt' => '0',
        'status' => ReservationStatus::Option->value,
    ]);

    $response->assertRedirect(route('reservations.index'));

    $reservation = Reservation::where('product', 'Standard reservation')->firstOrFail();
    expect($reservation->type)->toBe(ReservationType::Standard);
    expect((float) $reservation->discount)->toBe(100.0);
    expect((float) $reservation->commission)->toBe(50.0);
});

it('rejects an invalid type value', function () {
    $client = Client::factory()->create();
    $placement = Placement::factory()->create();

    $response = $this->post(route('reservations.store'), [
        'client_id' => $client->id,
        'product' => 'Bad type',
        'placement_id' => $placement->id,
        'type' => 'invalid_type',
        'channel' => 'Run of site',
        'scope' => 'Mauritius only',
        'dates_booked' => json_encode(['2026-05-01']),
        'gross_amount' => 1000,
        'total_amount_to_pay' => 1150,
        'vat_exempt' => '0',
        'status' => ReservationStatus::Option->value,
    ]);

    $response->assertSessionHasErrors('type');
    expect(Reservation::where('product', 'Bad type')->exists())->toBeFalse();
});

it('displays the reservation type label on the show page', function () {
    $reservation = Reservation::factory()->create(['type' => ReservationType::CostOfArtwork]);

    $this->get(route('reservations.show', $reservation))
        ->assertOk()
        ->assertSee('Cost of Artwork');
});
