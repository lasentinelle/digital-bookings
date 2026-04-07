<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\Platform;
use App\Models\Reservation;
use App\Models\Salesperson;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Show the dashboard with budget and sales KPIs.
     */
    public function index(): View
    {
        $now = Carbon::now();
        $financialYearStart = Budget::financialYearStartYear($now);
        $financialYearStartDate = Carbon::create($financialYearStart, Budget::FINANCIAL_YEAR_START_MONTH, 1)->startOfDay();
        $financialYearEndDate = $financialYearStartDate->copy()->addYear()->subDay()->endOfDay();
        $previousFinancialYearStartDate = $financialYearStartDate->copy()->subYear();
        $previousFinancialYearEndDate = $financialYearStartDate->copy()->subDay()->endOfDay();
        $financialYearLabel = Budget::financialYearLabel($financialYearStart);
        $previousFinancialYearLabel = Budget::financialYearLabel($financialYearStart - 1);

        $yearlyBudget = (float) Budget::forFinancialYear($financialYearStart)->sum('amount');

        $currentMonthBudget = (float) Budget::query()
            ->where('year', $now->year)
            ->where('month', $now->month)
            ->value('amount');

        $currentMonthSales = (float) Reservation::query()
            ->whereBetween('created_at', [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()])
            ->sum('gross_amount');

        $currentMonthPercentage = $currentMonthBudget > 0
            ? ($currentMonthSales / $currentMonthBudget) * 100
            : 0;

        $cumulatedSales = (float) Reservation::query()
            ->where('created_at', '>=', $financialYearStartDate)
            ->where('created_at', '<=', $now)
            ->sum('gross_amount');

        $yearlyPercentage = $yearlyBudget > 0
            ? ($cumulatedSales / $yearlyBudget) * 100
            : 0;

        $salespersonStats = $this->salespersonStats($financialYearStartDate, $financialYearEndDate);

        $monthlySalesComparison = $this->monthlySalesComparison(
            $financialYearStart,
            $financialYearStartDate,
            $financialYearEndDate,
            $previousFinancialYearStartDate,
            $previousFinancialYearEndDate,
        );

        $platformComparison = $this->platformComparison(
            $financialYearStartDate,
            $financialYearEndDate,
            $previousFinancialYearStartDate,
            $previousFinancialYearEndDate,
        );

        return view('home', compact(
            'yearlyBudget',
            'currentMonthBudget',
            'currentMonthSales',
            'currentMonthPercentage',
            'cumulatedSales',
            'yearlyPercentage',
            'financialYearLabel',
            'previousFinancialYearLabel',
            'financialYearStartDate',
            'salespersonStats',
            'monthlySalesComparison',
            'platformComparison',
        ));
    }

    /**
     * Bookings and sales totals per salesperson for the current financial year.
     *
     * @return Collection<int, Salesperson>
     */
    private function salespersonStats(Carbon $fyStart, Carbon $fyEnd): Collection
    {
        return Salesperson::query()
            ->withCount(['reservations as bookings_count' => function ($query) use ($fyStart, $fyEnd) {
                $query->whereBetween('created_at', [$fyStart, $fyEnd]);
            }])
            ->withSum(['reservations as sales_total' => function ($query) use ($fyStart, $fyEnd) {
                $query->whereBetween('created_at', [$fyStart, $fyEnd]);
            }], 'gross_amount')
            ->orderByDesc('sales_total')
            ->get();
    }

    /**
     * Monthly sales for the current and previous financial years, aligned by FY month index.
     *
     * @return list<array{label: string, current: float, previous: float}>
     */
    private function monthlySalesComparison(
        int $financialYearStart,
        Carbon $fyStart,
        Carbon $fyEnd,
        Carbon $prevFyStart,
        Carbon $prevFyEnd,
    ): array {
        $currentByYearMonth = $this->monthlyTotals($fyStart, $fyEnd);
        $previousByYearMonth = $this->monthlyTotals($prevFyStart, $prevFyEnd);

        $rows = [];
        for ($i = 0; $i < 12; $i++) {
            $currentMonth = Carbon::create($financialYearStart, Budget::FINANCIAL_YEAR_START_MONTH, 1)->addMonths($i);
            $previousMonth = $currentMonth->copy()->subYear();

            $rows[] = [
                'label' => $currentMonth->format('M'),
                'current' => (float) ($currentByYearMonth[$currentMonth->year.'-'.$currentMonth->month] ?? 0),
                'previous' => (float) ($previousByYearMonth[$previousMonth->year.'-'.$previousMonth->month] ?? 0),
            ];
        }

        return $rows;
    }

    /**
     * Group reservation gross_amount totals by year-month within a date range.
     *
     * @return array<string, float>
     */
    private function monthlyTotals(Carbon $start, Carbon $end): array
    {
        return Reservation::query()
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw("strftime('%Y', created_at) as y, strftime('%m', created_at) as m, SUM(gross_amount) as total")
            ->groupBy('y', 'm')
            ->get()
            ->mapWithKeys(fn ($row) => [((int) $row->y).'-'.((int) $row->m) => (float) $row->total])
            ->all();
    }

    /**
     * Sales totals per platform for the current and previous financial years.
     *
     * @return list<array{name: string, current: float, previous: float}>
     */
    private function platformComparison(
        Carbon $fyStart,
        Carbon $fyEnd,
        Carbon $prevFyStart,
        Carbon $prevFyEnd,
    ): array {
        $platforms = Platform::query()->orderBy('name')->get();

        $currentTotals = $this->platformTotals($fyStart, $fyEnd);
        $previousTotals = $this->platformTotals($prevFyStart, $prevFyEnd);

        return $platforms->map(fn (Platform $platform) => [
            'name' => $platform->name,
            'current' => (float) ($currentTotals[$platform->id] ?? 0),
            'previous' => (float) ($previousTotals[$platform->id] ?? 0),
        ])->all();
    }

    /**
     * @return array<int, float>
     */
    private function platformTotals(Carbon $start, Carbon $end): array
    {
        return Reservation::query()
            ->whereBetween('created_at', [$start, $end])
            ->whereNotNull('platform_id')
            ->selectRaw('platform_id, SUM(gross_amount) as total')
            ->groupBy('platform_id')
            ->pluck('total', 'platform_id')
            ->map(fn ($total) => (float) $total)
            ->all();
    }
}
