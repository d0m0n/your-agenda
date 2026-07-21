<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $organization = Organization::create([
            'name' => 'サンプル青年会議所',
        ]);

        User::factory()->create([
            'organization_id' => $organization->id,
            'role' => UserRole::General,
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        User::factory()->create([
            'organization_id' => $organization->id,
            'role' => UserRole::Observer,
            'name' => 'Observer User',
            'email' => 'observer@example.com',
        ]);
    }
}
