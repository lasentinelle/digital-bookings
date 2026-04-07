<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('salesperson_targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('budget_id')->constrained()->cascadeOnDelete();
            $table->foreignId('salesperson_id')->constrained('salespeople')->cascadeOnDelete();
            $table->decimal('amount', 14, 2)->unsigned()->default(0);
            $table->timestamps();

            $table->unique(['budget_id', 'salesperson_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salesperson_targets');
    }
};
