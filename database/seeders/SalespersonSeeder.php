<?php

namespace Database\Seeders;

use App\Models\Salesperson;
use Illuminate\Database\Seeder;

class SalespersonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $salespeople = [
            [
                'first_name' => 'Patricia',
                'last_name' => 'Caprice',
                'email' => 'patricia.caprice@lasentinelle.mu',
                'phone' => '+230 5000 0001',
                'sage_salesperson_code' => 'PATR',
            ],
            [
                'first_name' => 'Jenna',
                'last_name' => 'Moutou',
                'email' => 'jenna.moutou@lasentinelle.mu',
                'phone' => '+230 5000 0002',
                'sage_salesperson_code' => 'JENN',
            ],
            [
                'first_name' => 'Gino',
                'last_name' => 'Sophine',
                'email' => 'gino.sophie@lasentinelle.mu',
                'phone' => '+230 5000 0003',
                'sage_salesperson_code' => 'GINO',
            ],
            [
                'first_name' => 'Rachel',
                'last_name' => 'Dauhoo',
                'email' => 'rachel.dauhoo@lasentinelle.mu',
                'phone' => '+230 5000 0004',
                'sage_salesperson_code' => 'RACH',
            ],
        ];

        foreach ($salespeople as $salesperson) {
            Salesperson::firstOrCreate(
                ['email' => $salesperson['email']],
                $salesperson,
            );
        }
    }
}
