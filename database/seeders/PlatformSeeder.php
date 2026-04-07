<?php

namespace Database\Seeders;

use App\Models\Platform;
use Illuminate\Database\Seeder;

class PlatformSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $platforms = [
            ['name' => 'lexpress.mu'],
            ['name' => '5plus.mu'],
        ];

        foreach ($platforms as $platform) {
            Platform::firstOrCreate(
                ['name' => $platform['name']],
                $platform,
            );
        }
    }
}
