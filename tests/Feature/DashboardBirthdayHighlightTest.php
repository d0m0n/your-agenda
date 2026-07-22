<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardBirthdayHighlightTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_member_whose_birthday_is_today_is_marked_with_the_today_badge(): void
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->for($organization, 'organization')->create();

        $today = Member::factory()->for($organization, 'organization')->create([
            'name' => '本日誕生日メンバー',
            'birth_date' => now()->subYears(30),
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSeeInOrder(['本日誕生日メンバー', '本日']);
    }

    public function test_a_member_whose_birthday_is_later_this_month_is_not_marked_as_today(): void
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->for($organization, 'organization')->create();

        // Pick a day later in the current month; skip the test around month-end
        // where "later this month" might not exist, to avoid flakiness.
        $laterDay = now()->day + 1;
        if ($laterDay > now()->daysInMonth) {
            $this->markTestSkipped('No later day available in the current month.');
        }

        $member = Member::factory()->for($organization, 'organization')->create([
            'name' => '今月後半の誕生日メンバー',
            'birth_date' => now()->setDay($laterDay)->subYears(25),
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee($member->name);
        $response->assertDontSee('本日');
    }
}
