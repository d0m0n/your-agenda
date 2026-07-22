<?php

namespace Tests\Feature\Auth;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ObserverLoginIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_observer_and_general_user_sharing_a_password_each_log_in_as_themselves(): void
    {
        $organization = Organization::factory()->create();

        $general = User::factory()->for($organization)->create([
            'email' => 'general@example.com',
            'password' => Hash::make('shared-password'),
        ]);

        $observer = User::factory()->for($organization)->observer()->create([
            'email' => 'observer@example.com',
            'password' => Hash::make('shared-password'),
        ]);

        $this->post(route('login'), [
            'email' => 'observer@example.com',
            'password' => 'shared-password',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($observer);
        $this->assertTrue($this->app['auth']->user()->isObserver());

        $this->post(route('logout'))->assertRedirect('/');

        $this->post(route('login'), [
            'email' => 'general@example.com',
            'password' => 'shared-password',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($general);
        $this->assertTrue($this->app['auth']->user()->isGeneral());
    }

    public function test_observer_email_must_be_globally_unique_even_across_organizations(): void
    {
        $organization = Organization::factory()->create();
        User::factory()->for($organization)->create(['email' => 'taken@example.com']);

        $this->assertDatabaseHas('users', ['email' => 'taken@example.com']);

        $this->expectException(\Illuminate\Database\QueryException::class);

        User::factory()->for(Organization::factory())->observer()->create(['email' => 'taken@example.com']);
    }
}
