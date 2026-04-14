<?php

namespace App\Services;

use App\CommissionType;
use App\DiscountType;
use App\Models\Reservation;
use App\ReservationStatus;
use App\ReservationType;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Build the SAGE-formatted CSV rows for a date range.
 *
 * The Credit export produces, per client:
 *   V;LG01;INV;1;{sage_client_code};{YYYYMMDD today};{dd-Mon-yyyy} To {dd-Mon-yyyy}
 * followed by, per reservation (one pair per line item):
 *   D;MUTLTIM;1;{gross_in_range};{commission_pct};{discount_pct};{sage_salesperson_code};{product} | Ref. No {reference}
 *   LC;DPT;PRD;SNM;MUL
 *
 * Rules:
 * - Only Confirmed reservations are included.
 * - Only the days within [start, end] count towards the gross.
 * - If bill_at_end_of_campaign is set and the campaign ends after the range, the reservation is excluded entirely.
 * - For Cost of Artwork reservations, gross = reservation.gross_amount (no daily-rate × days math).
 */
class SageCsvBuilder
{
    public function __construct(
        private readonly Carbon $start,
        private readonly Carbon $end,
        private readonly ?Carbon $generatedAt = null,
    ) {}

    /**
     * Build the rows for every confirmed reservation in the given collection.
     *
     * @param  Collection<int, Reservation>  $reservations
     * @return array<int, array<int, string>>
     */
    public function build(Collection $reservations): array
    {
        $now = $this->generatedAt ?? Carbon::now();

        /** @var Collection<int, Reservation> $eligible */
        $eligible = $reservations
            ->filter(fn (Reservation $r) => $r->status === ReservationStatus::Confirmed)
            ->filter(fn (Reservation $r) => ! $this->isExcludedByBillAtEnd($r))
            ->map(function (Reservation $r) {
                $r->setAttribute('__dates_in_range', $this->datesInRange($r));

                return $r;
            })
            ->filter(fn (Reservation $r) => $this->hasChargeableDates($r));

        $grouped = $eligible
            ->sortBy(fn (Reservation $r) => [$r->client?->sage_client_code ?? 'zzz', $r->client?->company_name ?? ''])
            ->groupBy('client_id');

        $rows = [];
        foreach ($grouped as $clientReservations) {
            /** @var Collection<int, Reservation> $clientReservations */
            $client = $clientReservations->first()->client;

            $rows[] = [
                'V',
                'LG01',
                'INV',
                '1',
                (string) ($client?->sage_client_code ?? ''),
                $now->format('Ymd'),
                $this->start->format('d-M-Y').' To '.$this->end->format('d-M-Y'),
            ];

            foreach ($clientReservations as $reservation) {
                $gross = $this->grossInRange($reservation);
                $commissionPct = $this->commissionPercent($reservation, $gross);
                $discountPct = $this->discountPercent($reservation, $gross);

                $rows[] = [
                    'D',
                    'MUTLTIM',
                    '1',
                    $this->formatNumber($gross),
                    $this->formatNumber($commissionPct),
                    $this->formatNumber($discountPct),
                    (string) ($reservation->salesperson?->sage_salesperson_code ?? ''),
                    trim(($reservation->product ?? '').' | Ref. No '.($reservation->reference ?? '')),
                ];

                $rows[] = ['LC', 'DPT', 'PRD', 'SNM', 'MUL'];
            }
        }

        return $rows;
    }

    /**
     * @return Collection<int, Carbon>
     */
    private function datesInRange(Reservation $reservation): Collection
    {
        return collect($reservation->dates_booked ?? [])
            ->map(fn ($date) => $date instanceof Carbon ? $date : Carbon::parse((string) $date))
            ->filter(fn (Carbon $date) => $date->betweenIncluded($this->start, $this->end))
            ->values();
    }

    private function isExcludedByBillAtEnd(Reservation $reservation): bool
    {
        if (! $reservation->bill_at_end_of_campaign) {
            return false;
        }

        $lastBooked = collect($reservation->dates_booked ?? [])
            ->map(fn ($date) => $date instanceof Carbon ? $date : Carbon::parse((string) $date))
            ->max();

        if ($lastBooked === null) {
            return false;
        }

        return $lastBooked->greaterThan($this->end);
    }

    private function hasChargeableDates(Reservation $reservation): bool
    {
        /** @var Collection<int, Carbon> $datesInRange */
        $datesInRange = $reservation->getAttribute('__dates_in_range');

        return $datesInRange->isNotEmpty();
    }

    private function grossInRange(Reservation $reservation): float
    {
        /** @var Collection<int, Carbon> $datesInRange */
        $datesInRange = $reservation->getAttribute('__dates_in_range');

        if ($reservation->type === ReservationType::CostOfArtwork) {
            return (float) $reservation->gross_amount;
        }

        $dailyRate = (float) ($reservation->placement?->price ?? 0);

        return round($dailyRate * $datesInRange->count(), 2);
    }

    private function commissionPercent(Reservation $reservation, float $grossInRange): float
    {
        if ($reservation->type === ReservationType::CostOfArtwork) {
            return 0.0;
        }

        $client = $reservation->client;
        if ($client === null) {
            return 0.0;
        }

        $amount = (float) ($client->commission_amount ?? 0);
        if ($amount <= 0) {
            return 0.0;
        }

        if ($client->commission_type === CommissionType::Percentage) {
            return round($amount, 2);
        }

        if ($grossInRange <= 0) {
            return 0.0;
        }

        return round(($amount / $grossInRange) * 100, 2);
    }

    private function discountPercent(Reservation $reservation, float $grossInRange): float
    {
        if ($reservation->type === ReservationType::CostOfArtwork) {
            return 0.0;
        }

        $client = $reservation->client;
        if ($client === null) {
            return 0.0;
        }

        $amount = (float) ($client->discount ?? 0);
        if ($amount <= 0) {
            return 0.0;
        }

        if ($client->discount_type === DiscountType::Percentage) {
            return round($amount, 2);
        }

        if ($grossInRange <= 0) {
            return 0.0;
        }

        return round(($amount / $grossInRange) * 100, 2);
    }

    private function formatNumber(float $value): string
    {
        if (floor($value) === $value) {
            return (string) (int) $value;
        }

        return rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.');
    }
}
