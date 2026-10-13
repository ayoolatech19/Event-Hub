<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ConcurrencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_one_booking_succeeds_for_the_last_ticket(): void
    {
        $event = Event::factory()->create([
            'capacity' => 1,
            'tickets_sold' => 0,
            'status' => 'published',
        ]);

        $users = User::factory()->count(10)->create(['role' => 'user']);

        $successes = 0;

        foreach ($users as $user) {
            $response = $this->actingAs($user)
                ->postJson("/api/v1/events/{$event->id}/bookings", ['quantity' => 1]);

            if ($response->status() === 201) {
                $successes++;
            }
        }

        $this->assertSame(1, $successes);
        $this->assertSame(1, $event->fresh()->tickets_sold);
        $this->assertDatabaseCount('bookings', 1);
    }

    public function test_the_database_itself_rejects_overselling(): void
    {
        $event = Event::factory()->create([
            'capacity' => 5,
            'tickets_sold' => 5,
        ]);

        $this->expectException(QueryException::class);

        // bypass the application entirely
        DB::table('events')
            ->where('id', $event->id)
            ->update(['tickets_sold' => 6]);
    }
}