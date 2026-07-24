<?php

namespace Tests\Feature\Admin;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class AdminBasicAuthTest extends TestCase
{
    use RefreshDatabase;

    private function makeSuperAdmin(): User
    {
        return User::factory()->create([
            'organization_id' => null,
            'role' => UserRole::SuperAdmin,
        ]);
    }

    public function test_admin_panel_is_not_blocked_when_basic_auth_is_unconfigured(): void
    {
        Config::set('admin_security.basic_auth_username', null);
        Config::set('admin_security.basic_auth_password', null);

        $admin = $this->makeSuperAdmin();

        $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk();
    }

    public function test_admin_panel_challenges_request_without_basic_auth_credentials(): void
    {
        Config::set('admin_security.basic_auth_username', 'admin-user');
        Config::set('admin_security.basic_auth_password', 'admin-pass');

        $admin = $this->makeSuperAdmin();

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertStatus(401);
        $response->assertHeader('WWW-Authenticate', 'Basic realm="Admin Area"');
    }

    public function test_admin_panel_rejects_incorrect_basic_auth_credentials(): void
    {
        Config::set('admin_security.basic_auth_username', 'admin-user');
        Config::set('admin_security.basic_auth_password', 'admin-pass');

        $admin = $this->makeSuperAdmin();

        $response = $this->actingAs($admin)->get(route('admin.dashboard'), [
            'Authorization' => 'Basic '.base64_encode('admin-user:wrong-password'),
        ]);

        $response->assertStatus(401);
    }

    public function test_admin_panel_allows_access_with_correct_basic_auth_credentials(): void
    {
        Config::set('admin_security.basic_auth_username', 'admin-user');
        Config::set('admin_security.basic_auth_password', 'admin-pass');

        $admin = $this->makeSuperAdmin();

        $response = $this->actingAs($admin)->get(route('admin.dashboard'), [
            'Authorization' => 'Basic '.base64_encode('admin-user:admin-pass'),
        ]);

        $response->assertOk();
    }

    public function test_basic_auth_is_required_before_login_redirect_for_guests(): void
    {
        Config::set('admin_security.basic_auth_username', 'admin-user');
        Config::set('admin_security.basic_auth_password', 'admin-pass');

        $response = $this->get(route('admin.dashboard'));

        $response->assertStatus(401);
    }
}
