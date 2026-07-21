<?php

namespace Database\Factories;

use App\Models\Organization;
use App\Models\Site;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Site>
 */
class SiteFactory extends Factory
{
    protected $model = Site::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'uuid' => (string) Str::uuid(),
            'title' => fake()->sentence(3),
            'original_filename' => 'gian.zip',
            'index_path' => 'gian.htm',
            'user_id' => User::factory(),
        ];
    }
}
