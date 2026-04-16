<?php

use App\Models\Placement;
use App\Models\Platform;
use App\PlacementType;
use Database\Seeders\PlacementSeeder;
use Database\Seeders\PlatformSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('seeds a Facebook Boost placement for both lexpress.mu and 5plus.mu', function () {
    $this->seed(PlatformSeeder::class);
    $this->seed(PlacementSeeder::class);

    $lexpress = Platform::where('name', 'lexpress.mu')->firstOrFail();
    $fivePlus = Platform::where('name', '5plus.mu')->firstOrFail();

    $lexpressBoost = Placement::where('name', 'Facebook Boost')
        ->where('platform_id', $lexpress->id)
        ->first();

    $fivePlusBoost = Placement::where('name', 'Facebook Boost')
        ->where('platform_id', $fivePlus->id)
        ->first();

    expect($lexpressBoost)->not->toBeNull()
        ->and($lexpressBoost->type)->toBe(PlacementType::SocialMedia)
        ->and((int) $lexpressBoost->price)->toBe(0);

    expect($fivePlusBoost)->not->toBeNull()
        ->and($fivePlusBoost->type)->toBe(PlacementType::SocialMedia)
        ->and((int) $fivePlusBoost->price)->toBe(0);

    expect(Placement::where('name', 'Facebook Boost')->count())->toBe(2);
});
