<?php

use App\CommissionType;
use App\DiscountType;
use App\Models\Client;
use App\Models\Placement;
use App\Models\Reservation;
use App\Models\Salesperson;
use App\Models\User;
use App\ReservationStatus;
use App\ReservationType;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Parse the streamed CSV content into an array of rows.
 *
 * @return array<int, array<int, string>>
 */
function parseSageCsv(string $content): array
{
    $lines = array_values(array_filter(
        array_map('trim', explode("\n", $content)),
        fn (string $line) => $line !== '',
    ));

    return array_map(
        fn (string $line) => str_getcsv($line, ';', '"', '\\'),
        $lines,
    );
}

it('forbids non-admin users from exporting', function () {
    $salesperson = User::factory()->salesperson()->create();

    $this->actingAs($salesperson)
        ->get(route('reservations.sage-export', [
            'start_date' => '2026-04-01',
            'end_date' => '2026-04-30',
            'payment_mode' => 'credit',
        ]))
        ->assertForbidden();
});

it('streams a CSV download for credit mode as an admin', function () {
    $admin = User::factory()->admin()->create();

    $client = Client::factory()->create([
        'company_name' => 'Acme',
        'sage_client_code' => 'CLI001',
    ]);
    $salesperson = Salesperson::factory()->create(['sage_salesperson_code' => 'SP01']);
    $placement = Placement::factory()->create(['price' => 1000]);

    Reservation::factory()->create([
        'client_id' => $client->id,
        'salesperson_id' => $salesperson->id,
        'placement_id' => $placement->id,
        'type' => ReservationType::Standard,
        'status' => ReservationStatus::Confirmed,
        'dates_booked' => ['2026-04-05', '2026-04-06', '2026-04-07'],
        'bill_at_end_of_campaign' => false,
    ]);

    $response = $this->actingAs($admin)->get(route('reservations.sage-export', [
        'start_date' => '2026-04-01',
        'end_date' => '2026-04-30',
        'payment_mode' => 'credit',
    ]));

    $response->assertSuccessful();
    expect($response->headers->get('Content-Type'))->toContain('text/csv');

    $disposition = $response->headers->get('Content-Disposition');
    expect($disposition)->toContain('attachment');
    expect($disposition)->toContain('sage-export-20260401-20260430.csv');
});

it('builds V+D+LC rows for a single confirmed reservation', function () {
    $admin = User::factory()->admin()->create();

    $client = Client::factory()->create([
        'company_name' => 'Acme',
        'sage_client_code' => 'CLI001',
        'commission_amount' => null,
        'commission_type' => null,
        'discount' => null,
        'discount_type' => null,
    ]);
    $salesperson = Salesperson::factory()->create(['sage_salesperson_code' => 'SP01']);
    $placement = Placement::factory()->create(['price' => 1000]);

    Reservation::factory()->create([
        'client_id' => $client->id,
        'salesperson_id' => $salesperson->id,
        'placement_id' => $placement->id,
        'type' => ReservationType::Standard,
        'status' => ReservationStatus::Confirmed,
        'dates_booked' => ['2026-04-05', '2026-04-06', '2026-04-07'],
        'bill_at_end_of_campaign' => false,
    ]);

    $response = $this->actingAs($admin)->get(route('reservations.sage-export', [
        'start_date' => '2026-04-01',
        'end_date' => '2026-04-30',
        'payment_mode' => 'credit',
    ]));

    $response->assertSuccessful();
    $rows = parseSageCsv($response->streamedContent());

    expect($rows)->toHaveCount(3);

    expect($rows[0][0])->toBe('V');
    expect($rows[0][1])->toBe('LG01');
    expect($rows[0][2])->toBe('INV');
    expect($rows[0][3])->toBe('1');
    expect($rows[0][4])->toBe('CLI001');

    expect($rows[1][0])->toBe('D');
    expect($rows[1][1])->toBe('MUTLTIM');
    expect($rows[1][2])->toBe('1');
    expect($rows[1][3])->toBe('3000');

    expect($rows[2])->toBe(['LC', 'DPT', 'PRD', 'SNM', 'MUL']);
});

it('emits one V header and a D+LC pair per reservation for the same client', function () {
    $admin = User::factory()->admin()->create();

    $client = Client::factory()->create([
        'sage_client_code' => 'CLI001',
        'commission_amount' => null,
        'discount' => null,
    ]);
    $salesperson = Salesperson::factory()->create(['sage_salesperson_code' => 'SP01']);
    $placement = Placement::factory()->create(['price' => 1000]);

    Reservation::factory()->count(2)->create([
        'client_id' => $client->id,
        'salesperson_id' => $salesperson->id,
        'placement_id' => $placement->id,
        'type' => ReservationType::Standard,
        'status' => ReservationStatus::Confirmed,
        'dates_booked' => ['2026-04-05', '2026-04-06'],
        'bill_at_end_of_campaign' => false,
    ]);

    $response = $this->actingAs($admin)->get(route('reservations.sage-export', [
        'start_date' => '2026-04-01',
        'end_date' => '2026-04-30',
        'payment_mode' => 'credit',
    ]));

    $rows = parseSageCsv($response->streamedContent());

    expect($rows)->toHaveCount(5);
    expect($rows[0][0])->toBe('V');
    expect($rows[1][0])->toBe('D');
    expect($rows[2][0])->toBe('LC');
    expect($rows[3][0])->toBe('D');
    expect($rows[4][0])->toBe('LC');
});

it('groups by client and orders blocks by sage_client_code ascending', function () {
    $admin = User::factory()->admin()->create();

    $clientA = Client::factory()->create([
        'sage_client_code' => 'AAA001',
        'commission_amount' => null,
        'discount' => null,
    ]);
    $clientB = Client::factory()->create([
        'sage_client_code' => 'BBB001',
        'commission_amount' => null,
        'discount' => null,
    ]);
    $salesperson = Salesperson::factory()->create(['sage_salesperson_code' => 'SP01']);
    $placement = Placement::factory()->create(['price' => 500]);

    Reservation::factory()->create([
        'client_id' => $clientB->id,
        'salesperson_id' => $salesperson->id,
        'placement_id' => $placement->id,
        'type' => ReservationType::Standard,
        'status' => ReservationStatus::Confirmed,
        'dates_booked' => ['2026-04-05'],
        'bill_at_end_of_campaign' => false,
    ]);

    Reservation::factory()->create([
        'client_id' => $clientA->id,
        'salesperson_id' => $salesperson->id,
        'placement_id' => $placement->id,
        'type' => ReservationType::Standard,
        'status' => ReservationStatus::Confirmed,
        'dates_booked' => ['2026-04-05'],
        'bill_at_end_of_campaign' => false,
    ]);

    $response = $this->actingAs($admin)->get(route('reservations.sage-export', [
        'start_date' => '2026-04-01',
        'end_date' => '2026-04-30',
        'payment_mode' => 'credit',
    ]));

    $rows = parseSageCsv($response->streamedContent());

    expect($rows)->toHaveCount(6);
    expect($rows[0][0])->toBe('V');
    expect($rows[0][4])->toBe('AAA001');
    expect($rows[1][0])->toBe('D');
    expect($rows[2][0])->toBe('LC');
    expect($rows[3][0])->toBe('V');
    expect($rows[3][4])->toBe('BBB001');
    expect($rows[4][0])->toBe('D');
    expect($rows[5][0])->toBe('LC');
});

it('only counts booked dates that fall inside the export range towards the gross', function () {
    $admin = User::factory()->admin()->create();

    $client = Client::factory()->create([
        'sage_client_code' => 'CLI001',
        'commission_amount' => null,
        'discount' => null,
    ]);
    $salesperson = Salesperson::factory()->create(['sage_salesperson_code' => 'SP01']);
    $placement = Placement::factory()->create(['price' => 500]);

    Reservation::factory()->create([
        'client_id' => $client->id,
        'salesperson_id' => $salesperson->id,
        'placement_id' => $placement->id,
        'type' => ReservationType::Standard,
        'status' => ReservationStatus::Confirmed,
        'dates_booked' => [
            '2026-03-30',
            '2026-03-31',
            '2026-04-01',
            '2026-04-02',
            '2026-04-03',
            '2026-05-01',
        ],
        'bill_at_end_of_campaign' => false,
    ]);

    $response = $this->actingAs($admin)->get(route('reservations.sage-export', [
        'start_date' => '2026-04-01',
        'end_date' => '2026-04-30',
        'payment_mode' => 'credit',
    ]));

    $rows = parseSageCsv($response->streamedContent());

    expect($rows)->toHaveCount(3);
    expect($rows[1][0])->toBe('D');
    expect($rows[1][3])->toBe('1500');
});

it('excludes bill_at_end_of_campaign reservations whose campaign ends after the range', function () {
    $admin = User::factory()->admin()->create();

    $client = Client::factory()->create(['sage_client_code' => 'CLI001']);
    $salesperson = Salesperson::factory()->create(['sage_salesperson_code' => 'SP01']);
    $placement = Placement::factory()->create(['price' => 500]);

    Reservation::factory()->create([
        'client_id' => $client->id,
        'salesperson_id' => $salesperson->id,
        'placement_id' => $placement->id,
        'type' => ReservationType::Standard,
        'status' => ReservationStatus::Confirmed,
        'dates_booked' => ['2026-04-20', '2026-05-10'],
        'bill_at_end_of_campaign' => true,
    ]);

    $response = $this->actingAs($admin)->get(route('reservations.sage-export', [
        'start_date' => '2026-04-01',
        'end_date' => '2026-04-30',
        'payment_mode' => 'credit',
    ]));

    $rows = parseSageCsv($response->streamedContent());

    expect($rows)->toBeEmpty();
});

it('includes bill_at_end_of_campaign reservations whose campaign ends inside the range', function () {
    $admin = User::factory()->admin()->create();

    $client = Client::factory()->create([
        'sage_client_code' => 'CLI001',
        'commission_amount' => null,
        'discount' => null,
    ]);
    $salesperson = Salesperson::factory()->create(['sage_salesperson_code' => 'SP01']);
    $placement = Placement::factory()->create(['price' => 500]);

    Reservation::factory()->create([
        'client_id' => $client->id,
        'salesperson_id' => $salesperson->id,
        'placement_id' => $placement->id,
        'type' => ReservationType::Standard,
        'status' => ReservationStatus::Confirmed,
        'dates_booked' => ['2026-04-10', '2026-04-20'],
        'bill_at_end_of_campaign' => true,
    ]);

    $response = $this->actingAs($admin)->get(route('reservations.sage-export', [
        'start_date' => '2026-04-01',
        'end_date' => '2026-04-30',
        'payment_mode' => 'credit',
    ]));

    $rows = parseSageCsv($response->streamedContent());

    expect($rows)->toHaveCount(3);
    expect($rows[0][0])->toBe('V');
    expect($rows[1][0])->toBe('D');
    expect($rows[1][3])->toBe('1000');
    expect($rows[2][0])->toBe('LC');
});

it('excludes option and canceled reservations from the export', function () {
    $admin = User::factory()->admin()->create();

    $client = Client::factory()->create([
        'sage_client_code' => 'CLI001',
        'commission_amount' => null,
        'discount' => null,
    ]);
    $salesperson = Salesperson::factory()->create(['sage_salesperson_code' => 'SP01']);
    $placement = Placement::factory()->create(['price' => 1000]);

    Reservation::factory()->create([
        'client_id' => $client->id,
        'salesperson_id' => $salesperson->id,
        'placement_id' => $placement->id,
        'type' => ReservationType::Standard,
        'status' => ReservationStatus::Option,
        'dates_booked' => ['2026-04-05'],
        'bill_at_end_of_campaign' => false,
    ]);

    Reservation::factory()->create([
        'client_id' => $client->id,
        'salesperson_id' => $salesperson->id,
        'placement_id' => $placement->id,
        'type' => ReservationType::Standard,
        'status' => ReservationStatus::Canceled,
        'dates_booked' => ['2026-04-05'],
        'bill_at_end_of_campaign' => false,
    ]);

    Reservation::factory()->create([
        'client_id' => $client->id,
        'salesperson_id' => $salesperson->id,
        'placement_id' => $placement->id,
        'type' => ReservationType::Standard,
        'status' => ReservationStatus::Confirmed,
        'dates_booked' => ['2026-04-05'],
        'bill_at_end_of_campaign' => false,
    ]);

    $response = $this->actingAs($admin)->get(route('reservations.sage-export', [
        'start_date' => '2026-04-01',
        'end_date' => '2026-04-30',
        'payment_mode' => 'credit',
    ]));

    $rows = parseSageCsv($response->streamedContent());

    expect($rows)->toHaveCount(3);
    expect($rows[0][0])->toBe('V');
    expect($rows[1][0])->toBe('D');
    expect($rows[2][0])->toBe('LC');
});

it('emits an empty sage code column when the client has no sage_client_code', function () {
    $admin = User::factory()->admin()->create();

    $client = Client::factory()->create([
        'sage_client_code' => null,
        'commission_amount' => null,
        'discount' => null,
    ]);
    $salesperson = Salesperson::factory()->create(['sage_salesperson_code' => 'SP01']);
    $placement = Placement::factory()->create(['price' => 1000]);

    Reservation::factory()->create([
        'client_id' => $client->id,
        'salesperson_id' => $salesperson->id,
        'placement_id' => $placement->id,
        'type' => ReservationType::Standard,
        'status' => ReservationStatus::Confirmed,
        'dates_booked' => ['2026-04-05'],
        'bill_at_end_of_campaign' => false,
    ]);

    $response = $this->actingAs($admin)->get(route('reservations.sage-export', [
        'start_date' => '2026-04-01',
        'end_date' => '2026-04-30',
        'payment_mode' => 'credit',
    ]));

    $rows = parseSageCsv($response->streamedContent());

    expect($rows)->toHaveCount(3);
    expect($rows[0][0])->toBe('V');
    expect($rows[0][1])->toBe('LG01');
    expect($rows[0][2])->toBe('INV');
    expect($rows[0][3])->toBe('1');
    expect($rows[0][4])->toBe('');
});

it('uses the billing client sage code when the reservation represents another client', function () {
    $admin = User::factory()->admin()->create();

    $billingClient = Client::factory()->create([
        'sage_client_code' => 'BILL01',
        'commission_amount' => 10,
        'commission_type' => CommissionType::Percentage,
        'discount' => null,
    ]);
    $endBrand = Client::factory()->create([
        'sage_client_code' => 'END01',
        'commission_amount' => null,
        'discount' => null,
    ]);
    $salesperson = Salesperson::factory()->create(['sage_salesperson_code' => 'SP01']);
    $placement = Placement::factory()->create(['price' => 1000]);

    Reservation::factory()->create([
        'client_id' => $billingClient->id,
        'represented_client_id' => $endBrand->id,
        'salesperson_id' => $salesperson->id,
        'placement_id' => $placement->id,
        'type' => ReservationType::Standard,
        'status' => ReservationStatus::Confirmed,
        'dates_booked' => ['2026-04-05'],
        'bill_at_end_of_campaign' => false,
    ]);

    $response = $this->actingAs($admin)->get(route('reservations.sage-export', [
        'start_date' => '2026-04-01',
        'end_date' => '2026-04-30',
        'payment_mode' => 'credit',
    ]));

    $rows = parseSageCsv($response->streamedContent());

    expect($rows)->toHaveCount(3);
    expect($rows[0][0])->toBe('V');
    expect($rows[0][4])->toBe('BILL01');
});

it('redirects with an error flash when payment_mode is cash', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->get(route('reservations.sage-export', [
        'start_date' => '2026-04-01',
        'end_date' => '2026-04-30',
        'payment_mode' => 'cash',
    ]));

    $response->assertRedirect(route('reservations.index'));
    expect(session('error'))->toContain('Cash SAGE export is not yet available');
});

it('rejects requests missing start_date, end_date, or payment_mode', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->get(route('reservations.sage-export', [
        'start_date' => '',
        'end_date' => '',
        'payment_mode' => 'bogus',
    ]));

    $response->assertRedirect();
    $response->assertSessionHasErrors(['start_date', 'end_date', 'payment_mode']);
});

it('converts a fixed-amount (MUR) client commission into a percentage of the in-range gross', function () {
    $admin = User::factory()->admin()->create();

    $client = Client::factory()->create([
        'sage_client_code' => 'CLI001',
        'commission_amount' => 1500,
        'commission_type' => CommissionType::Mur,
        'discount' => null,
    ]);
    $salesperson = Salesperson::factory()->create(['sage_salesperson_code' => 'SP01']);
    $placement = Placement::factory()->create(['price' => 1000]);

    Reservation::factory()->create([
        'client_id' => $client->id,
        'salesperson_id' => $salesperson->id,
        'placement_id' => $placement->id,
        'type' => ReservationType::Standard,
        'status' => ReservationStatus::Confirmed,
        'dates_booked' => ['2026-04-05', '2026-04-06', '2026-04-07'],
        'bill_at_end_of_campaign' => false,
    ]);

    $response = $this->actingAs($admin)->get(route('reservations.sage-export', [
        'start_date' => '2026-04-01',
        'end_date' => '2026-04-30',
        'payment_mode' => 'credit',
    ]));

    $rows = parseSageCsv($response->streamedContent());

    expect($rows[1][0])->toBe('D');
    expect($rows[1][3])->toBe('3000');
    expect($rows[1][4])->toBe('50');
});

it('uses gross_amount and zeroes commission/discount for cost-of-artwork reservations', function () {
    $admin = User::factory()->admin()->create();

    $client = Client::factory()->create([
        'sage_client_code' => 'CLI001',
        'commission_amount' => 20,
        'commission_type' => CommissionType::Percentage,
        'discount' => 10,
        'discount_type' => DiscountType::Percentage,
    ]);
    $salesperson = Salesperson::factory()->create(['sage_salesperson_code' => 'SP01']);
    $placement = Placement::factory()->create(['price' => 1000]);

    Reservation::factory()->create([
        'client_id' => $client->id,
        'salesperson_id' => $salesperson->id,
        'placement_id' => $placement->id,
        'type' => ReservationType::CostOfArtwork,
        'status' => ReservationStatus::Confirmed,
        'dates_booked' => ['2026-04-05'],
        'gross_amount' => 5000,
        'bill_at_end_of_campaign' => false,
    ]);

    $response = $this->actingAs($admin)->get(route('reservations.sage-export', [
        'start_date' => '2026-04-01',
        'end_date' => '2026-04-30',
        'payment_mode' => 'credit',
    ]));

    $rows = parseSageCsv($response->streamedContent());

    expect($rows)->toHaveCount(3);
    expect($rows[1][0])->toBe('D');
    expect($rows[1][3])->toBe('5000');
    expect($rows[1][4])->toBe('0');
    expect($rows[1][5])->toBe('0');
});
