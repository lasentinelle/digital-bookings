<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('renders the SAGE export form for admin users', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->get(route('reservations.index'));

    $response->assertOk()
        ->assertSee('SAGE Export')
        ->assertSee('name="start_date"', false)
        ->assertSee('name="end_date"', false)
        ->assertSee('name="payment_mode"', false)
        ->assertSee('value="credit"', false)
        ->assertSee('value="cash"', false);
});

it('hides the SAGE export form for users who cannot sage-export', function () {
    $salesperson = User::factory()->salesperson()->create();

    $response = $this->actingAs($salesperson)->get(route('reservations.index'));

    $response->assertOk()
        ->assertDontSee('SAGE Export')
        ->assertSee('Add Reservation');
});

it('shows the Cash TBD error flash when the controller redirects back with error', function () {
    $admin = User::factory()->admin()->create();
    $message = 'Cash SAGE export is not yet available — the format is pending from accounting.';

    $response = $this->actingAs($admin)
        ->withSession(['error' => $message])
        ->get(route('reservations.index'));

    $response->assertOk()
        ->assertSee($message);
});
