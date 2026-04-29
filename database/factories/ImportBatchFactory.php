<?php

namespace Database\Factories;

use App\Models\Business;
use App\Models\ImportBatch;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ImportBatch>
 */
class ImportBatchFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'business_id' => Business::factory(),
            'original_filename' => 'freeagent-sample.csv',
            'stored_filename' => 'imports/freeagent-sample.csv',
            'imported_at' => now(),
            'total_rows' => 4,
            'valid_rows' => 4,
            'invalid_rows' => 0,
        ];
    }
}
