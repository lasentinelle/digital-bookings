@extends('layouts.main')

@section('content')
  <main class="flex-1 bg-white">
    <div class="px-12 py-10">
      <div class="flex items-center gap-4">
        <a href="{{ route('salespeople.index') }}" class="text-gray-400 hover:text-gray-600">
          <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
            <path fill-rule="evenodd" d="M17 10a.75.75 0 0 1-.75.75H5.612l4.158 3.96a.75.75 0 1 1-1.04 1.08l-5.5-5.25a.75.75 0 0 1 0-1.08l5.5-5.25a.75.75 0 0 1 1.04 1.08L5.612 9.25H16.25A.75.75 0 0 1 17 10Z" clip-rule="evenodd" />
          </svg>
        </a>
        <h1 class="text-2xl font-semibold text-gray-900">Edit Salesperson</h1>
      </div>
      <div class="mt-6 h-px w-full bg-gray-100"></div>

      <form action="{{ route('salespeople.update', $salesperson) }}" method="POST" class="mt-8 max-w-xl space-y-6">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-2 gap-6">
          <div>
            <label for="first_name" class="block text-sm font-medium text-gray-700">First Name</label>
            <div class="mt-2">
              <input type="text" name="first_name" id="first_name" value="{{ old('first_name', $salesperson->first_name) }}" required
                class="block w-full rounded-lg border @error('first_name') border-red-500 @else border-gray-200 @enderror bg-white px-4 py-2.5 text-gray-900 shadow-sm placeholder:text-gray-400 focus:border-gray-300 focus:outline-none focus:ring-4 focus:ring-gray-100" />
            </div>
            @error('first_name')
              <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
          </div>

          <div>
            <label for="last_name" class="block text-sm font-medium text-gray-700">Last Name</label>
            <div class="mt-2">
              <input type="text" name="last_name" id="last_name" value="{{ old('last_name', $salesperson->last_name) }}" required
                class="block w-full rounded-lg border @error('last_name') border-red-500 @else border-gray-200 @enderror bg-white px-4 py-2.5 text-gray-900 shadow-sm placeholder:text-gray-400 focus:border-gray-300 focus:outline-none focus:ring-4 focus:ring-gray-100" />
            </div>
            @error('last_name')
              <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
          </div>
        </div>

        <div>
          <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
          <div class="mt-2">
            <input type="email" name="email" id="email" value="{{ old('email', $salesperson->email) }}" required
              class="block w-full rounded-lg border @error('email') border-red-500 @else border-gray-200 @enderror bg-white px-4 py-2.5 text-gray-900 shadow-sm placeholder:text-gray-400 focus:border-gray-300 focus:outline-none focus:ring-4 focus:ring-gray-100" />
          </div>
          @error('email')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
          @enderror
        </div>

        <div>
          <label for="phone" class="block text-sm font-medium text-gray-700">Phone</label>
          <div class="mt-2">
            <input type="text" name="phone" id="phone" value="{{ old('phone', $salesperson->phone) }}" required
              class="block w-full rounded-lg border @error('phone') border-red-500 @else border-gray-200 @enderror bg-white px-4 py-2.5 text-gray-900 shadow-sm placeholder:text-gray-400 focus:border-gray-300 focus:outline-none focus:ring-4 focus:ring-gray-100" />
          </div>
          @error('phone')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
          @enderror
        </div>

        <div>
          <label for="sage_salesperson_code" class="block text-sm font-medium text-gray-700">SAGE Salesperson Code</label>
          <div class="mt-2">
            <input type="text" name="sage_salesperson_code" id="sage_salesperson_code" value="{{ old('sage_salesperson_code', $salesperson->sage_salesperson_code) }}" maxlength="50"
              class="block w-full rounded-lg border @error('sage_salesperson_code') border-red-500 @else border-gray-200 @enderror bg-white px-4 py-2.5 text-gray-900 shadow-sm placeholder:text-gray-400 focus:border-gray-300 focus:outline-none focus:ring-4 focus:ring-gray-100" />
          </div>
          @error('sage_salesperson_code')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
          @enderror
        </div>

        <div class="flex items-center gap-4">
          <button type="submit" class="rounded-lg bg-gray-900 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-gray-800 focus:outline-none focus:ring-4 focus:ring-gray-200">
            Update Salesperson
          </button>
          <a href="{{ route('salespeople.index') }}" class="text-sm font-medium text-gray-700 hover:text-gray-900">
            Cancel
          </a>
        </div>
      </form>
    </div>
  </main>
@endsection
