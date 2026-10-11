<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

class TicketController extends Controller
{
    // POST /tickets/verify — preview scan, changes nothing
    public function verify(Request $request)
    {
        $data = $request->validate(['code' => 'required|string']);

        $ticket = Ticket::with('booking.user', 'booking.event')
            ->where('code', $data['code'])
            ->first();

        if (! $ticket) {
            return response()->json(['message' => 'Invalid ticket'], 404);
        }

        Gate::authorize('checkIn', $ticket->booking->event);

        return response()->json([
            'data' => [
                'code' => $ticket->code,
                'status' => $ticket->status,
                'attendee' => $ticket->booking->user->name,
                'event' => $ticket->booking->event->title,
                'checked_in_at' => $ticket->checked_in_at,
            ],
        ]);
    }

    // POST /tickets/check-in — marks it used
    public function checkIn(Request $request)
    {
        $data = $request->validate(['code' => 'required|string']);

        $ticket = Ticket::with('booking.event')->where('code', $data['code'])->first();

        if (! $ticket) {
            return response()->json(['message' => 'Invalid ticket'], 404);
        }

        $event = $ticket->booking->event;

        Gate::authorize('checkIn', $event);

        if ($event->status === 'cancelled') {
            return response()->json(['message' => 'Event cancelled'], 422);
        }

        return DB::transaction(function () use ($ticket, $request) {
            $locked = Ticket::whereKey($ticket->id)->lockForUpdate()->first();

            if ($locked->status === 'used') {
                return response()->json([
                    'message' => 'Ticket already used at ' . $locked->checked_in_at->format('H:i'),
                ], 409);
            }

            if ($locked->status === 'void') {
                return response()->json(['message' => 'Ticket is void'], 422);
            }

            $locked->update([
                'status' => 'used',
                'checked_in_at' => now(),
                'checked_in_by' => $request->user()->id,
            ]);

            Log::info('Ticket checked in', [
                'code' => $locked->code,
                'by' => $request->user()->id,
            ]);

            return response()->json(['message' => 'Checked in successfully'], 200);
        });
    }

    // GET /events/{event}/attendance
    public function attendance(Event $event)
    {
        Gate::authorize('checkIn', $event);

        $total = $event->tickets()->count();
        $checkedIn = $event->tickets()->where('tickets.status', 'used')->count();

        return response()->json([
            'data' => [
                'event' => $event->title,
                'total_tickets' => $total,
                'checked_in' => $checkedIn,
                'remaining' => $total - $checkedIn,
            ],
        ]);
    }
}