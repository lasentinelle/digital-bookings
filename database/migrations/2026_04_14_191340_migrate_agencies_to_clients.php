<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $agencyToClient = [];

        foreach (DB::table('agencies')->get() as $agency) {
            $existing = DB::table('clients')
                ->where('brn', $agency->brn)
                ->orWhere(function ($q) use ($agency) {
                    $q->whereNull('brn')->where('company_name', $agency->company_name);
                })
                ->first();

            if ($existing) {
                $clientId = $existing->id;
            } else {
                $clientId = DB::table('clients')->insertGetId([
                    'company_name' => $agency->company_name,
                    'company_logo' => $agency->company_logo ?? null,
                    'brn' => $agency->brn,
                    'vat_number' => $agency->vat_number ?? null,
                    'vat_exempt' => $agency->vat_exempt ?? false,
                    'phone' => $agency->phone,
                    'address' => $agency->address,
                    'commission_amount' => $agency->commission_amount ?? null,
                    'commission_type' => $agency->commission_type ?? null,
                    'discount' => $agency->discount ?? null,
                    'discount_type' => $agency->discount_type ?? null,
                    'contact_person_name' => $agency->contact_person_name ?? null,
                    'contact_person_email' => $agency->contact_person_email ?? null,
                    'contact_person_phone' => $agency->contact_person_phone ?? null,
                    'created_at' => $agency->created_at,
                    'updated_at' => $agency->updated_at,
                ]);
            }

            $agencyToClient[$agency->id] = $clientId;
        }

        foreach (DB::table('reservations')->whereNotNull('agency_id')->get() as $reservation) {
            if (! isset($agencyToClient[$reservation->agency_id])) {
                continue;
            }
            DB::table('reservations')
                ->where('id', $reservation->id)
                ->update([
                    'represented_client_id' => $reservation->client_id,
                    'client_id' => $agencyToClient[$reservation->agency_id],
                ]);
        }

        DB::table('reservations')->update(['agency_id' => null]);
    }

    public function down(): void
    {
        // Irreversible data migration.
    }
};
