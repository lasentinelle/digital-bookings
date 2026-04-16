<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('agency_id');
        });

        Schema::dropIfExists('agencies');
    }

    public function down(): void
    {
        // Irreversible; re-running would require rebuilding the agency table.
    }
};
