<x-mail::message>
# See you tomorrow

Hi {{ $booking->user->name }},

**{{ $booking->event->title }}** starts on {{ $booking->event->date->format('d M Y, g:i A') }}.

- **Venue:** {{ $booking->event->venue }}
- **Tickets:** {{ $booking->quantity }}

Bring your QR code — you'll find it in your bookings.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>