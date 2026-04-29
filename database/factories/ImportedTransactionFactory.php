<?php

namespace Database\Factories;

use App\Models\Business;
use App\Models\CategoryMapping;
use App\Models\ImportBatch;
use App\Models\ImportedTransaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ImportedTransaction>
 */
class ImportedTransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $isIncome = fake()->boolean();
        $amount = fake()->randomFloat(2, 10, 2000);

        return [
            'import_batch_id' => ImportBatch::factory(),
            'business_id' => Business::factory(),
            'category_mapping_id' => CategoryMapping::factory(),
            'transaction_date' => '2025-05-01',
            'freeagent_category' => 'Sales',
            'description' => fake()->sentence(4),
            'amount' => $isIncome ? $amount : -$amount,
            'normalized_amount' => $isIncome ? $amount : -$amount,
            'income_or_expense' => $isIncome ? 'income' : 'expense',
            'tax_year_start' => 2025,
            'tax_year_quarter' => 1,
            'raw_row' => [
                'Date' => '2025-05-01',
                'Category' => 'Sales',
                'Description' => 'Factory row',
                'Amount' => (string) ($isIncome ? $amount : -$amount),
                'Type' => $isIncome ? 'income' : 'expense',
            ],
        ];
    }
}
