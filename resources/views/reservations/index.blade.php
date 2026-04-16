@extends('layouts.main')

@section('content')
  <main class="flex-1 bg-white">
    <div class="px-12 py-10">
      <div class="flex flex-wrap items-center justify-between gap-4">
        <h1 class="text-2xl font-semibold text-gray-900">Reservations</h1>
        <div class="flex flex-wrap items-center gap-3">
          @can('sage-export')
            <form method="GET" action="{{ route('reservations.sage-export') }}" class="flex flex-wrap items-center gap-2" x-data="{ start: '', end: '' }">
              <input type="date" name="start_date" x-model="start" required
                class="rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-gray-400 focus:ring-4 focus:ring-gray-100">
              <input type="date" name="end_date" x-model="end" required
                class="rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-gray-400 focus:ring-4 focus:ring-gray-100">
              <select name="payment_mode" class="rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-gray-400 focus:ring-4 focus:ring-gray-100">
                <option value="credit">Credit</option>
                <option value="cash">Cash</option>
              </select>
              <button type="submit" class="cursor-pointer rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-gray-800 focus:outline-none focus:ring-4 focus:ring-gray-200">
                SAGE Export
              </button>
            </form>
          @endcan
          <a href="{{ route('reservations.create') }}" class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-gray-800 focus:outline-none focus:ring-4 focus:ring-gray-200">
            Add Reservation
          </a>
        </div>
      </div>
      <div class="mt-6 h-px w-full bg-gray-100"></div>

      @if(session('success'))
        <div class="mt-6 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-800">
          {{ session('success') }}
        </div>
      @endif

      @if(session('error'))
        <div class="mt-6 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-800">
          {{ session('error') }}
        </div>
      @endif

      <div class="mt-6">
        <table class="min-w-full divide-y divide-gray-200">
          <thead>
            <tr>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Reference</th>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Status</th>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Client</th>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Product</th>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Platform</th>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Placement</th>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Total to Pay</th>
              <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            @forelse($reservations as $reservation)
              <tr class="hover:bg-gray-50">
                <td class="px-4 py-3 text-sm">
                  <button
                    type="button"
                    x-data="copyToClipboard(@js($reservation->reference))"
                    @click="copy()"
                    :title="copied ? 'Copied!' : 'Click to copy'"
                    class="inline-flex items-center gap-1.5 rounded-md px-2 py-1 font-mono text-xs font-medium ring-1 ring-inset cursor-pointer hover:brightness-95 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-gray-300 {{ $reservation->status->referenceClasses() }}"
                  >
                    {{ $reservation->reference }}
                    <svg x-show="!copied" class="h-3 w-3 opacity-60" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M15.666 3.888A2.25 2.25 0 0 0 13.5 2.25h-3c-1.03 0-1.9.693-2.166 1.638m7.332 0c.055.194.084.4.084.612v0a.75.75 0 0 1-.75.75H9a.75.75 0 0 1-.75-.75v0c0-.212.03-.418.084-.612m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 0 1-2.25 2.25H6.75A2.25 2.25 0 0 1 4.5 19.5V6.257c0-1.108.806-2.057 1.907-2.185a48.208 48.208 0 0 1 1.927-.184" />
                    </svg>
                    <svg x-show="copied" x-cloak class="h-3 w-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="display: none;" aria-hidden="true">
                      <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                    </svg>
                  </button>
                </td>
                <td class="px-4 py-3 text-sm text-gray-600">
                  <span class="inline-flex items-center gap-2">
                    <span class="inline-block h-2.5 w-2.5 rounded-full {{ $reservation->status->dotClasses() }}"></span>
                    {{ $reservation->status->label() }}
                  </span>
                </td>
                <td class="px-4 py-3 text-sm text-gray-900">{{ $reservation->client->company_name }}</td>
                <td class="px-4 py-3 text-sm text-gray-600">{{ $reservation->product }}</td>
                <td class="px-4 py-3 text-sm text-gray-600">{{ $reservation->platform?->name ?? '—' }}</td>
                <td class="px-4 py-3 text-sm text-gray-600">{{ $reservation->placement->name }}</td>
                <td class="px-4 py-3 text-sm text-gray-600">MUR {{ number_format($reservation->total_amount_to_pay, 2) }}</td>
                <td class="px-4 py-3 text-right">
                  <div class="flex items-center justify-end gap-2">
                    <a href="{{ route('reservations.show', $reservation) }}" class="rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50">
                      View
                    </a>
                    <a href="{{ route('reservations.edit', $reservation) }}" class="rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50">
                      Edit
                    </a>
                    <form action="{{ route('reservations.destroy', $reservation) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this reservation?')">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="rounded-lg border border-red-200 px-3 py-1.5 text-xs font-medium text-red-700 hover:bg-red-50">
                        Delete
                      </button>
                    </form>
                  </div>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="8" class="px-4 py-10 text-center text-sm text-gray-500">
                  No reservations found. Click "Add Reservation" to create one.
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>

        @if($reservations->hasPages())
          <div class="mt-6">
            {{ $reservations->links() }}
          </div>
        @endif
      </div>
    </div>
  </main>
@endsection
