<?php

namespace Tests\Feature;

use App\Models\Meeting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesTenants;
use Tests\TestCase;

class MeetingVenueDetailsTest extends TestCase
{
    use CreatesTenants, RefreshDatabase;

    public function test_manager_can_save_venue_and_event_details(): void
    {
        [$organization, $general] = $this->createTenant();
        $meeting = Meeting::factory()->for($organization, 'organization')->create();

        $response = $this->actingAs($general)->put(route('meetings.update', $meeting), [
            'name' => $meeting->name,
            'venue_address' => '東京都千代田区1-1-1',
            'venue_map_url' => 'https://maps.google.com/?q=test',
            'social_event_info' => '懇親会 19:30〜',
            'recommended_hotel_info' => '〇〇ホテル',
        ]);

        $response->assertRedirect(route('meetings.edit', $meeting));
        $response->assertSessionHasNoErrors();

        $meeting->refresh();
        $this->assertSame('東京都千代田区1-1-1', $meeting->venue_address);
        $this->assertSame('https://maps.google.com/?q=test', $meeting->venue_map_url);
        $this->assertSame('懇親会 19:30〜', $meeting->social_event_info);
        $this->assertSame('〇〇ホテル', $meeting->recommended_hotel_info);
    }

    public function test_venue_map_url_must_be_a_valid_url(): void
    {
        [$organization, $general] = $this->createTenant();
        $meeting = Meeting::factory()->for($organization, 'organization')->create();

        $response = $this->actingAs($general)->put(route('meetings.update', $meeting), [
            'name' => $meeting->name,
            'venue_map_url' => 'not-a-url',
        ]);

        $response->assertSessionHasErrors('venue_map_url');
    }
}
