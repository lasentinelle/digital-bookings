@extends('layouts.main')

@php
  $typeLabels = [
    'reservation' => 'Reservation Reference',
    'client' => 'Client Name',
  ];
  $typeLabel = $typeLabels[$type] ?? 'Reservation Reference';
@endphp

@section('content')
  <main class="flex-1 bg-white">
    <div class="px-12 py-10">
      <div class="flex items-center justify-between">
        <h1 class="text-2xl font-semibold text-gray-900">Search Results</h1>
      </div>
      <div class="mt-6 h-px w-full bg-gray-100"></div>

      <div class="mt-6 text-sm text-gray-600">
        @if($query === '')
          Enter a search query from the sidebar Search button to get started.
        @else
          Searching for <span class="font-semibold text-gray-900">"{{ $query }}"</span> in <span class="font-semibold text-gray-900">{{ $typeLabel }}</span>
          @if($results)
            — {{ $results->total() }} {{ Str::plural('result', $results->total()) }} found
          @endif
        @endif
      </div>

      @if($results && $results->total() > 0)
        <div class="mt-6">
          <table class="min-w-full divide-y divide-gray-200">
            <thead>
              <tr>
                @if($type === 'reservation')
                  <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Reference</th>
                  <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Client</th>
                  <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Product</th>
                  <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Platform</th>
                  <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Placement</th>
                  <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Gross Amount</th>
                  <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">Actions</th>
                @else
                  <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Company</th>
                  <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">BRN</th>
                  <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Phone</th>
                  <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Contact Person</th>
                  <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">Actions</th>
                @endif
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              @foreach($results as $row)
                <tr class="hover:bg-gray-50">
                  @if($type === 'reservation')
                    <td class="px-4 py-3 text-sm font-mono text-gray-600">{{ $row->reference }}</td>
                    <td class="px-4 py-3 text-sm text-gray-900">{{ $row->client->company_name }}</td>
                    <td class="px-4 py-3 text-sm text-gray-600">{{ $row->product }}</td>
                    <td class="px-4 py-3 text-sm text-gray-600">{{ $row->platform?->name ?? '—' }}</td>
                    <td class="px-4 py-3 text-sm text-gray-600">{{ $row->placement->name }}</td>
                    <td class="px-4 py-3 text-sm text-gray-600">MUR {{ number_format($row->gross_amount, 2) }}</td>
                    <td class="px-4 py-3 text-right">
                      <a href="{{ route('reservations.show', $row) }}" class="rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50">
                        View
                      </a>
                    </td>
                  @else
                    <td class="px-4 py-3 text-sm text-gray-900">{{ $row->company_name }}</td>
                    <td class="px-4 py-3 text-sm text-gray-600">{{ $row->brn }}</td>
                    <td class="px-4 py-3 text-sm text-gray-600">{{ $row->phone }}</td>
                    <td class="px-4 py-3 text-sm text-gray-600">{{ $row->contact_person_name ?? '—' }}</td>
                    <td class="px-4 py-3 text-right">
                      <a href="{{ route('clients.show', $row) }}" class="rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50">
                        View
                      </a>
                    </td>
                  @endif
                </tr>
              @endforeach
            </tbody>
          </table>

          @if($results->hasPages())
            <div class="mt-6">
              {{ $results->links() }}
            </div>
          @endif
        </div>
      @elseif($results)
        <div class="mt-10 text-center text-sm text-gray-500">
          No results found for "{{ $query }}" in {{ $typeLabel }}.
        </div>
      @endif
    </div>
  </main>
@endsection
