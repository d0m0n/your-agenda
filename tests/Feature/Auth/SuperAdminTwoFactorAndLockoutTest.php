<?php

namespace Tests\Feature\Auth;

use App\Enums\UserRole;
use App\Models\User;
use App\Services\TwoFactorAuthenticationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use PragmaRX\Google2FA\Google2FA;
use Tests\TestCase;

class SuperAdminTwoFactorAndLockoutTest extends TestCase
{
    use RefreshDatabase;

    private function makeSuperAdmin(array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'organization_id' => null,
            'role' => UserRole::SuperAdmin,
            'password' => \Illuminate\Support\Facades\Hash::make('password12345'),
        ], $attributes));
    }

    private function currentOtp(string $secret): string
    {
        return (new Google2FA())->getCurrentOtp($secret);
    }

    /**
     * IPアドレス単位のログイン試行制限(Breeze標準、5回/分)がテスト内の
     * 連続リクエストで先にブロックしてしまい、アカウント単位のロック
     * (10回)まで到達できなくなるのを防ぐため、各試行前にクリアする。
     */
    private function clearIpThrottle(string $email): void
    {
        RateLimiter::clear(Str::transliterate(Str::lower($email).'|127.0.0.1'));
    }

    public function test_general_user_logs_in_directly_without_two_factor_challenge(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_super_admin_without_two_factor_is_redirected_to_mandatory_setup(): void
    {
        $admin = $this->makeSuperAdmin();

        $response = $this->post('/login', [
            'email' => $admin->email,
            'password' => 'password12345',
        ]);

        $this->assertGuest();
        $response->assertRedirect(route('two-factor.login'));

        $this->get(route('two-factor.login'))
            ->assertOk()
            ->assertSee(__('二段階認証の設定'));
    }

    public function test_super_admin_can_complete_two_factor_setup_and_receives_recovery_codes_once(): void
    {
        $admin = $this->makeSuperAdmin();

        $this->post('/login', ['email' => $admin->email, 'password' => 'password12345']);
        $this->get(route('two-factor.login'));

        $admin->refresh();
        $this->assertNotNull($admin->two_factor_secret);

        $code = $this->currentOtp($admin->two_factor_secret);

        $response = $this->post(route('two-factor.login.store'), ['code' => $code]);

        $this->assertAuthenticatedAs($admin->fresh());
        $response->assertRedirect(route('two-factor.recovery-codes'));

        $recoveryPage = $this->get(route('two-factor.recovery-codes'));
        $recoveryPage->assertOk();

        // 一度表示したら再アクセスではダッシュボードへ流れる(session flashが消費される)。
        $this->get(route('two-factor.recovery-codes'))->assertRedirect(route('dashboard'));
    }

    public function test_super_admin_with_confirmed_two_factor_is_challenged_on_login(): void
    {
        $service = app(TwoFactorAuthenticationService::class);
        $secret = $service->generateSecretKey();

        $admin = $this->makeSuperAdmin([
            'two_factor_secret' => $secret,
            'two_factor_confirmed_at' => now(),
            'two_factor_recovery_codes' => ['AAAA-BBBB'],
        ]);

        $response = $this->post('/login', ['email' => $admin->email, 'password' => 'password12345']);

        $this->assertGuest();
        $response->assertRedirect(route('two-factor.login'));

        $this->get(route('two-factor.login'))
            ->assertOk()
            ->assertSee(__('二段階認証'));

        $wrong = $this->post(route('two-factor.login.store'), ['code' => '000000']);
        $this->assertGuest();
        $wrong->assertSessionHasErrors('code');

        $code = $this->currentOtp($secret);
        $correct = $this->post(route('two-factor.login.store'), ['code' => $code]);

        $this->assertAuthenticatedAs($admin->fresh());
        $correct->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_super_admin_can_use_a_recovery_code_instead_of_totp(): void
    {
        $service = app(TwoFactorAuthenticationService::class);
        $secret = $service->generateSecretKey();

        $admin = $this->makeSuperAdmin([
            'two_factor_secret' => $secret,
            'two_factor_confirmed_at' => now(),
            'two_factor_recovery_codes' => ['AAAA-BBBB', 'CCCC-DDDD'],
        ]);

        $this->post('/login', ['email' => $admin->email, 'password' => 'password12345']);

        $response = $this->post(route('two-factor.login.store'), ['recovery_code' => 'aaaa-bbbb']);

        $this->assertAuthenticatedAs($admin->fresh());
        $response->assertRedirect(route('dashboard', absolute: false));

        $this->assertSame(['CCCC-DDDD'], $admin->fresh()->two_factor_recovery_codes);
    }

    public function test_used_recovery_code_cannot_be_reused(): void
    {
        $service = app(TwoFactorAuthenticationService::class);
        $secret = $service->generateSecretKey();

        $admin = $this->makeSuperAdmin([
            'two_factor_secret' => $secret,
            'two_factor_confirmed_at' => now(),
            'two_factor_recovery_codes' => ['AAAA-BBBB'],
        ]);

        $this->post('/login', ['email' => $admin->email, 'password' => 'password12345']);
        $this->post(route('two-factor.login.store'), ['recovery_code' => 'AAAA-BBBB']);
        $this->post('/logout');

        $this->post('/login', ['email' => $admin->email, 'password' => 'password12345']);
        $response = $this->post(route('two-factor.login.store'), ['recovery_code' => 'AAAA-BBBB']);

        $this->assertGuest();
        $response->assertSessionHasErrors('code');
    }

    public function test_super_admin_account_locks_after_ten_failed_password_attempts(): void
    {
        $admin = $this->makeSuperAdmin();

        for ($i = 0; $i < 10; $i++) {
            $this->clearIpThrottle($admin->email);
            $this->post('/login', ['email' => $admin->email, 'password' => 'wrong-password']);
        }

        $this->assertTrue($admin->fresh()->isLocked());

        $response = $this->post('/login', ['email' => $admin->email, 'password' => 'password12345']);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    public function test_locked_super_admin_account_unlocks_automatically_after_cooldown(): void
    {
        $admin = $this->makeSuperAdmin([
            'failed_login_attempts' => 10,
            'locked_until' => now()->subMinute(),
        ]);

        $response = $this->post('/login', ['email' => $admin->email, 'password' => 'password12345']);

        $response->assertRedirect(route('two-factor.login'));
    }

    public function test_general_user_login_failures_do_not_trigger_account_lock(): void
    {
        $user = User::factory()->create();

        for ($i = 0; $i < 10; $i++) {
            $this->clearIpThrottle($user->email);
            $this->post('/login', ['email' => $user->email, 'password' => 'wrong-password']);
        }

        $this->clearIpThrottle($user->email);
        $response = $this->post('/login', ['email' => $user->email, 'password' => 'password']);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }
}
