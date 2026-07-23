<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        $response = $this->post('/register', [
            'organization_name' => 'テスト青年会議所',
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));

        $this->assertDatabaseHas('organizations', ['name' => 'テスト青年会議所']);

        $user = User::where('email', 'test@example.com')->firstOrFail();
        $this->assertTrue($user->isGeneral());
        $this->assertSame($user->organization_id, $user->organization->id);
        $this->assertSame('テスト青年会議所', $user->organization->name);
    }

    public function test_registration_requires_organization_name(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors('organization_name');
        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['email' => 'test@example.com']);
    }

    public function test_each_registration_creates_its_own_organization(): void
    {
        $this->post('/register', [
            'organization_name' => '組織A',
            'name' => 'User A',
            'email' => 'a@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        // register は guest ミドルウェア配下のため、ログイン中は次の登録に進めない。
        $this->post('/logout');

        $this->post('/register', [
            'organization_name' => '組織B',
            'name' => 'User B',
            'email' => 'b@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $userA = User::where('email', 'a@example.com')->firstOrFail();
        $userB = User::where('email', 'b@example.com')->firstOrFail();

        $this->assertNotSame($userA->organization_id, $userB->organization_id);
    }
}
