<?php

namespace Tests\Feature;

use App\Models\Meeting;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MeetingSortTest extends TestCase
{
    use RefreshDatabase;

    private function namesInOrder(string $content, array $names): array
    {
        return collect($names)
            ->map(fn ($name) => strpos($content, $name))
            ->all();
    }

    public function test_meeting_index_defaults_to_newest_held_at_first(): void
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->for($organization, 'organization')->create();

        $earliest = Meeting::factory()->for($organization, 'organization')->create(['name' => '古い会議', 'held_at' => now()->subMonth()]);
        $latest = Meeting::factory()->for($organization, 'organization')->create(['name' => '新しい会議', 'held_at' => now()->addMonth()]);

        $response = $this->actingAs($user)->get(route('meetings.index'));

        $response->assertOk();
        $positions = $this->namesInOrder($response->getContent(), [$latest->name, $earliest->name]);
        $this->assertTrue($positions[0] < $positions[1], 'the meeting with the newer held_at appears first by default');
    }

    public function test_held_at_sort_can_be_switched_to_ascending(): void
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->for($organization, 'organization')->create();

        $earliest = Meeting::factory()->for($organization, 'organization')->create(['name' => '古い会議', 'held_at' => now()->subMonth()]);
        $latest = Meeting::factory()->for($organization, 'organization')->create(['name' => '新しい会議', 'held_at' => now()->addMonth()]);

        $response = $this->actingAs($user)->get(route('meetings.index', ['sort' => 'held_at', 'direction' => 'asc']));

        $response->assertOk();
        $positions = $this->namesInOrder($response->getContent(), [$earliest->name, $latest->name]);
        $this->assertTrue($positions[0] < $positions[1], 'ascending direction lists the older meeting first');
    }

    public function test_meetings_can_be_sorted_by_name(): void
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->for($organization, 'organization')->create();

        $b = Meeting::factory()->for($organization, 'organization')->create(['name' => 'B会議']);
        $a = Meeting::factory()->for($organization, 'organization')->create(['name' => 'A会議']);

        $response = $this->actingAs($user)->get(route('meetings.index', ['sort' => 'name', 'direction' => 'asc']));

        $response->assertOk();
        $positions = $this->namesInOrder($response->getContent(), [$a->name, $b->name]);
        $this->assertTrue($positions[0] < $positions[1]);
    }

    public function test_unknown_sort_column_falls_back_to_the_default(): void
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->for($organization, 'organization')->create();

        Meeting::factory()->for($organization, 'organization')->create(['name' => '会議A']);

        $response = $this->actingAs($user)->get(route('meetings.index', ['sort' => 'password']));

        $response->assertOk();
    }
}
