<?php

namespace Database\Seeders;

use App\Models\Budget;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class BudgetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $start = Carbon::create(2025, 7, 1);

        for ($i = 0; $i < 12; $i++) {
            $month = $start->copy()->addMonths($i);
            $amount = 1_500_000 + random_int(-300_000, 300_000);

            Budget::query()->updateOrCreate(
                ['year' => $month->year, 'month' => $month->month],
                ['amount' => $amount],
            );
        }
    }
}
