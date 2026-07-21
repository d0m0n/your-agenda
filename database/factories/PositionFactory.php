<?php

namespace Database\Factories;

use App\Models\Organization;
use App\Models\Position;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Position>
 */
class PositionFactory extends Factory
{
    protected $model = Position::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'serial_number' => fake()->unique()->numberBetween(1, 100000),
            'name' => fake()->jobTitle(),
        ];
    }
}
