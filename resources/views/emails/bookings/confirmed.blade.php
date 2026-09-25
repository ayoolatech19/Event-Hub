<x-mail::message>
# Booking Confirmed

Hi {{ $booking->user->name }},

Your booking for **{{ $booking->event->title }}** is confirmed.

- **Tickets:** {{ $booking->quantity }}
- **Total:** NGN {{ number_format($booking->total_price, 2) }}
- **Venue:** {{ $booking->event->venue }}
- **Date:** {{ $booking->event->date->format('d M Y, g:i A') }}

Please arrive a few minutes early with this confirmation.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>