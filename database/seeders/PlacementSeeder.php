<?php

namespace Database\Seeders;

use App\Models\Placement;
use App\Models\Platform;
use Illuminate\Database\Seeder;

class PlacementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $lexpress = Platform::where('name', 'lexpress.mu')->firstOrFail();
        $fivePlus = Platform::where('name', '5plus.mu')->firstOrFail();

        $placements = [
            ['name' => 'Top Billboard Banner Homepage', 'price' => 18000, 'platform_id' => $lexpress->id],
            ['name' => 'Centralised Pop-Up', 'price' => 10000, 'platform_id' => $lexpress->id],
            ['name' => 'Slider Image', 'price' => 7000, 'platform_id' => $lexpress->id],
            ['name' => 'Facebook Post', 'price' => 5000, 'platform_id' => $lexpress->id],
            ['name' => 'LinkedIn Post', 'price' => 2500, 'platform_id' => $lexpress->id],
            ['name' => 'Top Billboard Banner Homepage', 'price' => 10000, 'platform_id' => $fivePlus->id],
            ['name' => 'Facebook Post', 'price' => 3000, 'platform_id' => $fivePlus->id],
        ];

        foreach ($placements as $placement) {
            Placement::firstOrCreate(
                [
                    'name' => $placement['name'],
                    'platform_id' => $placement['platform_id'],
                ],
                $placement,
            );
        }
    }
}
