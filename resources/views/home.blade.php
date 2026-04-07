@extends('layouts.main')

@section('content')
  <main class="flex-1 bg-white">
    <div class="px-12 py-10">
      <div class="flex items-end justify-between">
        <h1 class="text-2xl font-semibold text-gray-900">Home</h1>
        <p class="text-sm text-gray-500">Financial year {{ $financialYearLabel }}</p>
      </div>
      <div class="mt-6 h-px w-full bg-gray-100"></div>

      {{-- KPI cards --}}
      <div class="mt-8 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
        {{-- Yearly Budget --}}
        <div class="rounded-2xl bg-white p-5 ring-1 ring-gray-200 shadow-sm">
          <p class="text-xs font-medium uppercase tracking-wider text-gray-500">Yearly Budget</p>
          <p class="mt-2 text-2xl font-semibold text-gray-900">MUR {{ number_format($yearlyBudget) }}</p>
          <p class="mt-1 text-xs text-gray-400">FY {{ $financialYearLabel }}</p>
        </div>

        {{-- Current Month Target --}}
        <div class="rounded-2xl bg-white p-5 ring-1 ring-gray-200 shadow-sm">
          <p class="text-xs font-medium uppercase tracking-wider text-gray-500">{{ now()->format('F') }} Target</p>
          <p class="mt-2 text-2xl font-semibold text-gray-900">MUR {{ number_format($currentMonthBudget) }}</p>
          <p class="mt-1 text-xs text-gray-400">Monthly budget</p>
        </div>

        {{-- Current Month Sales --}}
        <div class="rounded-2xl bg-white p-5 ring-1 ring-gray-200 shadow-sm">
          <p class="text-xs font-medium uppercase tracking-wider text-gray-500">{{ now()->format('F') }} Sales</p>
          <p class="mt-2 text-2xl font-semibold text-gray-900">MUR {{ number_format($currentMonthSales) }}</p>
          @php
            $monthPctClass = $currentMonthPercentage >= 100 ? 'text-green-600' : ($currentMonthPercentage >= 75 ? 'text-amber-600' : 'text-gray-500');
          @endphp
          <p class="mt-1 text-xs font-medium {{ $monthPctClass }}">
            {{ number_format($currentMonthPercentage, 1) }}% of target
          </p>
        </div>

        {{-- Cumulated Sales since FY start --}}
        <div class="rounded-2xl bg-white p-5 ring-1 ring-gray-200 shadow-sm">
          <p class="text-xs font-medium uppercase tracking-wider text-gray-500">Cumulated Sales</p>
          <p class="mt-2 text-2xl font-semibold text-gray-900">MUR {{ number_format($cumulatedSales) }}</p>
          <p class="mt-1 text-xs text-gray-400">Since {{ $financialYearStartDate->format('M Y') }}</p>
        </div>

        {{-- Yearly Target Achieved % --}}
        <div class="rounded-2xl bg-white p-5 ring-1 ring-gray-200 shadow-sm">
          <p class="text-xs font-medium uppercase tracking-wider text-gray-500">Yearly Target</p>
          @php
            $yearPctClass = $yearlyPercentage >= 100 ? 'text-green-600' : ($yearlyPercentage >= 75 ? 'text-amber-600' : 'text-gray-900');
          @endphp
          <p class="mt-2 text-2xl font-semibold {{ $yearPctClass }}">{{ number_format($yearlyPercentage, 1) }}%</p>
          <p class="mt-1 text-xs text-gray-400">Achieved</p>
          <div class="mt-3 h-1.5 w-full overflow-hidden rounded-full bg-gray-100">
            <div class="h-full bg-gray-900" style="width: {{ min(100, $yearlyPercentage) }}%"></div>
          </div>
        </div>
      </div>

      {{-- Second row: salesperson, monthly comparison, platform comparison --}}
      @php
        $monthlyMax = 0;
        foreach ($monthlySalesComparison as $monthRow) {
            $monthlyMax = max($monthlyMax, $monthRow['current'], $monthRow['previous']);
        }
      @endphp
      <div class="mt-8 grid grid-cols-1 gap-4 lg:grid-cols-4">
        {{-- Salesperson bookings & sales --}}
        <div class="rounded-2xl bg-white p-5 ring-1 ring-gray-200 shadow-sm lg:col-span-1">
          <p class="text-xs font-medium uppercase tracking-wider text-gray-500">Salesperson Performance</p>
          <p class="mt-1 text-xs text-gray-400">FY {{ $financialYearLabel }}</p>
          <div class="mt-4 space-y-3">
            @forelse($salespersonStats as $salesperson)
              <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                  <p class="truncate text-sm font-medium text-gray-900">
                    {{ $salesperson->first_name }} {{ $salesperson->last_name }}
                  </p>
                  <p class="text-xs text-gray-500">{{ (int) $salesperson->bookings_count }} bookings</p>
                </div>
                <p class="shrink-0 text-sm font-semibold text-gray-900">
                  MUR {{ number_format((float) $salesperson->sales_total) }}
                </p>
              </div>
            @empty
              <p class="text-sm text-gray-500">No salespersons yet.</p>
            @endforelse
          </div>
        </div>

        {{-- Monthly sales comparison bar chart --}}
        <div class="rounded-2xl bg-white p-5 ring-1 ring-gray-200 shadow-sm lg:col-span-2">
          <div class="flex items-start justify-between gap-3">
            <div>
              <p class="text-xs font-medium uppercase tracking-wider text-gray-500">Monthly Sales Comparison</p>
              <p class="mt-1 text-xs text-gray-400">FY {{ $financialYearLabel }} vs FY {{ $previousFinancialYearLabel }}</p>
            </div>
            <div class="flex items-center gap-3 text-xs text-gray-500">
              <span class="flex items-center gap-1.5">
                <span class="inline-block h-2.5 w-2.5 rounded-sm bg-gray-900"></span>
                {{ $financialYearLabel }}
              </span>
              <span class="flex items-center gap-1.5">
                <span class="inline-block h-2.5 w-2.5 rounded-sm bg-gray-300"></span>
                {{ $previousFinancialYearLabel }}
              </span>
            </div>
          </div>

          <div class="mt-6 grid grid-cols-12 items-end gap-2" style="height: 180px;">
            @foreach($monthlySalesComparison as $monthRow)
              @php
                $currentHeight = $monthlyMax > 0 ? ($monthRow['current'] / $monthlyMax) * 100 : 0;
                $previousHeight = $monthlyMax > 0 ? ($monthRow['previous'] / $monthlyMax) * 100 : 0;
              @endphp
              <div class="flex h-full flex-col items-center justify-end">
                <div class="flex h-full w-full items-end justify-center gap-0.5">
                  <div
                    class="w-1/2 rounded-t bg-gray-900"
                    style="height: {{ $currentHeight }}%;"
                    title="{{ $monthRow['label'] }} {{ $financialYearLabel }}: MUR {{ number_format($monthRow['current']) }}"
                  ></div>
                  <div
                    class="w-1/2 rounded-t bg-gray-300"
                    style="height: {{ $previousHeight }}%;"
                    title="{{ $monthRow['label'] }} {{ $previousFinancialYearLabel }}: MUR {{ number_format($monthRow['previous']) }}"
                  ></div>
                </div>
              </div>
            @endforeach
          </div>
          <div class="mt-2 grid grid-cols-12 gap-2">
            @foreach($monthlySalesComparison as $monthRow)
              <p class="text-center text-[10px] font-medium text-gray-500">{{ $monthRow['label'] }}</p>
            @endforeach
          </div>
        </div>

        {{-- Platform comparison --}}
        <div class="rounded-2xl bg-white p-5 ring-1 ring-gray-200 shadow-sm lg:col-span-1">
          <p class="text-xs font-medium uppercase tracking-wider text-gray-500">Platform Sales</p>
          <p class="mt-1 text-xs text-gray-400">FY {{ $financialYearLabel }} vs FY {{ $previousFinancialYearLabel }}</p>
          <div class="mt-4 space-y-4">
            @forelse($platformComparison as $platform)
              @php
                $diff = $platform['current'] - $platform['previous'];
                $diffPercent = $platform['previous'] > 0
                    ? ($diff / $platform['previous']) * 100
                    : ($platform['current'] > 0 ? 100 : 0);
                $diffClass = $diff >= 0 ? 'text-green-600' : 'text-red-600';
                $diffPrefix = $diff >= 0 ? '+' : '';
              @endphp
              <div>
                <div class="flex items-center justify-between gap-3">
                  <p class="truncate text-sm font-medium text-gray-900">{{ $platform['name'] }}</p>
                  <p class="text-xs font-medium {{ $diffClass }}">{{ $diffPrefix }}{{ number_format($diffPercent, 1) }}%</p>
                </div>
                <div class="mt-1 flex items-baseline justify-between gap-2">
                  <p class="text-sm font-semibold text-gray-900">MUR {{ number_format($platform['current']) }}</p>
                  <p class="text-xs text-gray-400">prev MUR {{ number_format($platform['previous']) }}</p>
                </div>
              </div>
            @empty
              <p class="text-sm text-gray-500">No platforms yet.</p>
            @endforelse
          </div>
        </div>
      </div>
    </div>
  </main>
@endsection
