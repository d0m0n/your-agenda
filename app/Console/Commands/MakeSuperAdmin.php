<?php

namespace App\Console\Commands;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

/**
 * The only way to create a super_admin account: there is no self-registration
 * flow (mirrors how observer/general accounts are seeded via tinker), and a
 * platform admin doesn't belong to any single organization.
 */
class MakeSuperAdmin extends Command
{
    protected $signature = 'admin:create-super-admin {name} {email} {password}';

    protected $description = '管理者アカウント(super_admin)を作成する';

    public function handle(): int
    {
        $data = [
            'name' => $this->argument('name'),
            'email' => $this->argument('email'),
            'password' => $this->argument('password'),
        ];

        $validator = Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        User::create([
            'organization_id' => null,
            'role' => UserRole::SuperAdmin,
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        $this->info("管理者アカウント {$data['email']} を作成しました。");

        return self::SUCCESS;
    }
}
