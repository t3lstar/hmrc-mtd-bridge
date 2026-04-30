<?php

namespace Database\Factories;

use App\Models\Business;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Business>
 */
class BusinessFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->company(),
            'ownership_percentage' => fake()->randomElement([25, 50, 75, 100]),
            'is_active' => true,
            'freeagent_client_id' => null,
            'freeagent_client_secret' => null,
            'freeagent_refresh_token' => null,
            'freeagent_access_token' => null,
            'freeagent_access_token_expires_at' => null,
        ];
    }
}
