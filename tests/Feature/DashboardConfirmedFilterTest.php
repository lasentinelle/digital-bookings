<?php

use App\Models\Budget;
use App\Models\Client;
use App\Models\Placement;
use App\Models\Platform;
use App\Models\Reservation;
use App\Models\Salesperson;
use App\Models\User;
use App\ReservationStatus;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('only counts confirmed reservations on the dashboard earnings', function () {
    $admin = User::factory()->admin()->create();
    $platform = Platform::factory()->create(['name' => 'lexpress.mu']);
    $client = Client::factory()->create();
    $placement = Placement::factory()->create(['platform_id' => $platform->id]);
    $salesperson = Salesperson::factory()->create();

    $fyStart = Budget::financialYearStartYear();
    $insideCurrentFy = Carbon::create($fyStart, Budget::FINANCIAL_YEAR_START_MONTH, 15);

    $confirmedAmount = 12345;
    $optionAmount = 99999;
    $canceledAmount = 88888;

    $cases = [
        ['amount' => $confirmedAmount, 'status' => ReservationStatus::Confirmed],
        ['amount' => $optionAmount, 'status' => ReservationStatus::Option],
        ['amount' => $canceledAmount, 'status' => ReservationStatus::Canceled],
    ];

    foreach ($cases as $case) {
        $reservation = Reservation::create([
            'client_id' => $client->id,
            'salesperson_id' => $salesperson->id,
            'platform_id' => $platform->id,
            'placement_id' => $placement->id,
            'product' => 'Test product',
            'channel' => 'Run of site',
            'scope' => 'Mauritius only',
            'dates_booked' => [$insideCurrentFy->format('Y-m-d')],
            'gross_amount' => $case['amount'],
            'total_amount_to_pay' => $case['amount'],
            'discount' => 0,
            'commission' => 0,
            'vat' => 0,
            'vat_exempt' => false,
            'status' => $case['status'],
        ]);
        $reservation->created_at = $insideCurrentFy;
        $reservation->updated_at = $insideCurrentFy;
        $reservation->saveQuietly();
    }

    $response = $this->actingAs($admin)
        ->get(route('home'))
        ->assertOk();

    $response->assertSee('MUR '.number_format($confirmedAmount));
    $response->assertDontSee('MUR '.number_format($optionAmount));
    $response->assertDontSee('MUR '.number_format($canceledAmount));
});
