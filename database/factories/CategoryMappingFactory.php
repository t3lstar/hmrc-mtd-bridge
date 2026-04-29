<?php

namespace Database\Factories;

use App\Models\CategoryMapping;
use App\Models\HmrcCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<CategoryMapping>
 */
class CategoryMappingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $category = fake()->unique()->words(2, true);

        return [
            'lookup_key' => Str::lower($category),
            'freeagent_category' => Str::title($category),
            'hmrc_category_id' => HmrcCategory::factory(),
            'needs_review' => false,
            'notes' => null,
        ];
    }
}
