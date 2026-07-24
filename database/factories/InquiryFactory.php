<?php

namespace Database\Factories;

use App\Enums\InquiryCategory;
use App\Models\Inquiry;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Inquiry>
 */
class InquiryFactory extends Factory
{
    protected $model = Inquiry::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'user_id' => User::factory(),
            'category' => fake()->randomElement(InquiryCategory::cases()),
            'subject' => fake()->sentence(4),
            'body' => fake()->paragraph(),
        ];
    }

    public function handled(): static
    {
        return $this->state(fn (array $attributes) => [
            'handled_at' => now(),
        ]);
    }
}
