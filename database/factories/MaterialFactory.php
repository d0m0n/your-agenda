<?php

namespace Database\Factories;

use App\Models\Material;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Material>
 */
class MaterialFactory extends Factory
{
    protected $model = Material::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'title' => fake()->sentence(3),
            'file_path' => 'materials/'.Str::uuid().'.pdf',
            'original_filename' => fake()->word().'.pdf',
            'user_id' => User::factory(),
        ];
    }
}
