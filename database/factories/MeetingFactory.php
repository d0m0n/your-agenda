<?php

namespace Database\Factories;

use App\Models\Meeting;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Meeting>
 */
class MeetingFactory extends Factory
{
    protected $model = Meeting::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'name' => fake()->monthName().'例会',
            'held_at' => fake()->dateTimeBetween('now', '+2 months'),
            'location' => fake()->address(),
        ];
    }
}
