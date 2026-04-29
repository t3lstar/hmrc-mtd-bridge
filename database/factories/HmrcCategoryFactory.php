<?php

namespace Database\Factories;

use App\Models\HmrcCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HmrcCategory>
 */
class HmrcCategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $reportType = fake()->randomElement(['self_employment', 'uk_property']);
        $categoryType = fake()->randomElement(['income', 'expense']);

        return [
            'code' => strtoupper(fake()->unique()->lexify('CAT???')),
            'name' => fake()->words(3, true),
            'report_type' => $reportType,
            'category_type' => $categoryType,
            'sort_order' => fake()->numberBetween(1, 50),
        ];
    }
}
