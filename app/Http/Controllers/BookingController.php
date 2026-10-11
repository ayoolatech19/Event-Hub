<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookingRequest;
use App\Http\Resources\BookingResource;
use App\Models\Booking;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use App\Mail\BookingConfirmed;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BookingController extends Controller
{
    // POST /events/{event}/bookings
    public function store(StoreBookingRequest $request, Event $event)
    {
        if ($event->status !== 'published') {
            abort(404);
        }

        $quantity = $request->validated()['quantity'];

        $booking = DB::transaction(function () use ($request, $event, $quantity) {
            $event = Event::whereKey($event->id)->lockForUpdate()->first();

            $remaining = $event->capacity - $event->tickets_sold;

            if ($quantity > $remaining) {
                abort(response()->json([
                    'message' => "Only {$remaining} ticket(s) left for this event.",
                ], 422));
            }

            $event->increment('tickets_sold', $quantity);

            return $request->user()->bookings()->create([
                'event_id' => $event->id,
                'quantity' => $quantity,
                'total_price' => $event->price * $quantity,
                'reference' => 'EVH-' . Str::upper(Str::random(16)),
                'status' => 'pending',
                'expires_at' => now()->addMinutes(15),
                ]);

        });
return (new BookingResource($booking))
    ->response()
    ->setStatusCode(201);
    }

    // GET /bookings
    public function index(Request $request)
    {
        $bookings = $request->user()
            ->bookings()
            ->with('event.category')
            ->latest()
            ->get()
            ->with('event.category', 'tickets');

        return BookingResource::collection($bookings);
    }

    // DELETE /bookings/{booking}
    public function destroy(Booking $booking)
    {
        Gate::authorize('delete', $booking);

        if ($booking->status === 'cancelled') {
            return response()->json(['message' => 'Booking is already cancelled.'], 422);
        }
      
    DB::transaction(function () use ($booking) {
        $booking->event()->lockForUpdate()->first();
        $booking->update(['status' => 'cancelled']);
        $booking->event->decrement('tickets_sold', $booking->quantity);
    });

        $booking->update(['status' => 'cancelled']);

        return response()->json(['message' => 'Booking cancelled successfully.']);
    }

    // GET /events/{event}/bookings
    public function eventBookings(Event $event)
    {
        Gate::authorize('viewBookings', $event);

        $bookings = $event->bookings()->with('user')->latest()->get();

        return BookingResource::collection($bookings);
    }
}