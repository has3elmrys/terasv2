<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CurrencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
                $now = now();

        // rate_to_pivot = 1 unit currency ini = berapa USD. USD sendiri = pivot internal, selalu 1.
        // Placeholder - ketimpa job fetch pertama kali jalan, jangan dianggap kurs akurat hari ini.

                DB::table('currencies')->insert([
            ['code' => 'USD', 'name' => 'US Dollar',          'symbol' => '$',  'rate_to_pivot' => '1.000000000000', 'decimal_places' => 2, 'is_active' => true, 'rate_updated_at' => $now, 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'IDR', 'name' => 'Indonesian Rupiah',  'symbol' => 'Rp', 'rate_to_pivot' => '0.000061000000', 'decimal_places' => 0, 'is_active' => true, 'rate_updated_at' => $now, 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'EUR', 'name' => 'Euro',                'symbol' => '€',  'rate_to_pivot' => '1.080000000000', 'decimal_places' => 2, 'is_active' => true, 'rate_updated_at' => $now, 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'GBP', 'name' => 'British Pound',       'symbol' => '£',  'rate_to_pivot' => '1.270000000000', 'decimal_places' => 2, 'is_active' => true, 'rate_updated_at' => $now, 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'SGD', 'name' => 'Singapore Dollar',    'symbol' => 'S$', 'rate_to_pivot' => '0.740000000000', 'decimal_places' => 2, 'is_active' => true, 'rate_updated_at' => $now, 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'AUD', 'name' => 'Australian Dollar',   'symbol' => 'A$', 'rate_to_pivot' => '0.650000000000', 'decimal_places' => 2, 'is_active' => true, 'rate_updated_at' => $now, 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'MYR', 'name' => 'Malaysian Ringgit',   'symbol' => 'RM', 'rate_to_pivot' => '0.210000000000', 'decimal_places' => 2, 'is_active' => true, 'rate_updated_at' => $now, 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'JPY', 'name' => 'Japanese Yen',        'symbol' => '¥',  'rate_to_pivot' => '0.006700000000', 'decimal_places' => 0, 'is_active' => true, 'rate_updated_at' => $now, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }
}
