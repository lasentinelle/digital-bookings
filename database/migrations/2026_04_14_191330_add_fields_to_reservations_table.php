<?php

use App\ReservationType;
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
        Schema::table('reservations', function (Blueprint $table) {
            $table->string('type')->default(ReservationType::Standard->value)->after('placement_id');
            $table->boolean('is_cash')->default(false)->after('vat_exempt');
            $table->foreignId('parent_reservation_id')->nullable()->after('is_cash')
                ->constrained('reservations')->nullOnDelete();
            $table->foreignId('represented_client_id')->nullable()->after('client_id')
                ->constrained('clients')->nullOnDelete();
            $table->boolean('is_foreign_currency')->default(false)->after('is_cash');
            $table->decimal('foreign_currency_amount', 12, 2)->nullable()->after('is_foreign_currency');
            $table->string('foreign_currency_code', 3)->nullable()->after('foreign_currency_amount');
            $table->boolean('bill_at_end_of_campaign')->default(false)->after('foreign_currency_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('parent_reservation_id');
            $table->dropConstrainedForeignId('represented_client_id');
            $table->dropColumn([
                'type',
                'is_cash',
                'is_foreign_currency',
                'foreign_currency_amount',
                'foreign_currency_code',
                'bill_at_end_of_campaign',
            ]);
        });
    }
};
