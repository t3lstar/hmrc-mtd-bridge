<?php

namespace Database\Seeders;

use App\Models\Business;
use Illuminate\Database\Seeder;

class BusinessSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach ([
            ['name' => 'iBPM', 'ownership_percentage' => 50.00],
            ['name' => 'NOSWAD LLP', 'ownership_percentage' => 100.00],
        ] as $business) {
            Business::query()->updateOrCreate(
                ['name' => $business['name']],
                $business + ['is_active' => true],
            );
        }
    }
}
