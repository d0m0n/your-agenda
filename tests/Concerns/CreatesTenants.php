<?php

namespace Tests\Concerns;

use App\Models\Organization;
use App\Models\User;

/**
 * Spins up an isolated organization with its general/observer users, for
 * asserting that one organization can never reach another's data.
 */
trait CreatesTenants
{
    /**
     * @return array{0: Organization, 1: User, 2: User} [organization, general user, observer user]
     */
    protected function createTenant(): array
    {
        $organization = Organization::factory()->create();
        $general = User::factory()->for($organization)->create();
        $observer = User::factory()->for($organization)->observer()->create();

        return [$organization, $general, $observer];
    }
}
