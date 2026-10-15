# EventHub API

A REST API for event booking, built with Laravel. Organizers create events, users book tickets and pay with Paystack, and tickets are checked in at the door with QR codes.

Built as a Laravel API task, then extended with real payments and production hardening.

## Stack

- Laravel, PHP
- PostgreSQL (the original spec said MySQL; PostgreSQL was used for CHECK constraint support)
- Laravel Sanctum for token authentication
- Paystack for payments
- Database queue driver for background jobs
- endroid/qr-code for ticket QR codes

## Features

- **Auth and roles**: register, login, logout, `/auth/me`. Roles are `admin`, `organizer` and `user`. New registrations are always `user`; the admin is created by a seeder.
- **Categories**: public listing; only admins can create, update or delete.
- **Events**: organizers manage only their own events (enforced with a Policy). The public only sees published events. Input is validated with Form Requests; banners are jpg/png, max 2MB.
- **Listing**: `GET /events` is paginated (10 per page), searchable by title (`?search=`) and filterable by category (`?category=`).
- **Bookings**: 1–5 tickets per booking, protected against overselling (see below). Users view and cancel their own bookings; organizers view bookings for their events.
- **Payments**: Paystack checkout with verified webhooks.
- **QR tickets**: one unguessable ticket per seat, with a check-in endpoint that can't be scanned twice.
- **Emails**: booking confirmation sent through the queue.
- **Scheduled jobs**: unpaid bookings expire and release their seats; ticket holders get one reminder before the event.
- **Responses**: every response goes through an API Resource. Passwords and tokens are never returned. Errors are always JSON.

## Setup

```bash
git clone https://github.com/<your-handle>/event_hub.git
cd event_hub
composer install
cp .env.example .env
php artisan key:generate
```

Set your database and keys in `.env`:

```
DB_CONNECTION=pgsql
DB_DATABASE=event_hub
DB_USERNAME=postgres
DB_PASSWORD=

QUEUE_CONNECTION=database
MAIL_MAILER=log

PAYSTACK_SECRET_KEY=sk_test_xxx
```

Then:

```bash
php artisan migrate --seed
php artisan storage:link
php artisan serve
```

The seeder creates the admin account:

| Email | Password |
| --- | --- |
| admin@eventhub.test | password |

## Running the background processes

`php artisan queue:work` processes jobs the application pushed onto the queue:
confirmation emails, QR generation, payment processing. It reacts to things
users did.

`php artisan schedule:work` runs commands on a clock, whether or not anyone is
using the app: expiring unpaid bookings every minute, sending event reminders
daily at 09:00.

Production needs both. One handles work triggered by users, the other handles
work triggered by time. In production the queue worker runs under a process
supervisor (Supervisor or systemd) so it restarts if it dies, and the scheduler
is a single cron entry running `php artisan schedule:run` every minute.

With `MAIL_MAILER=log`, emails are written to `storage/logs/laravel.log`.

## API

All routes are under `/api/v1`. Send `Accept: application/json` on every request, and `Authorization: Bearer <token>` on protected ones.

| Method | Endpoint | Access |
| --- | --- | --- |
| POST | `/auth/register` | Public |
| POST | `/auth/login` | Public |
| POST | `/auth/logout` | Logged in |
| GET | `/auth/me` | Logged in |
| GET | `/categories` | Public |
| POST / PUT / DELETE | `/categories` | Admin |
| GET | `/events` | Public (published only) |
| POST / PUT / DELETE | `/events` | Organizer (own events) |
| POST | `/events/{event}/bookings` | User |
| GET | `/bookings` | User (own bookings) |
| DELETE | `/bookings/{booking}` | User (own bookings) |
| GET | `/events/{event}/bookings` | Organizer (own events) |

A Postman collection is included in the repo: `<path-to-collection>.json`.

## Design decisions

### Money is stored in kobo

All money is in kobo (the smallest naira unit). NGN 5,000.00 is sent and stored
as `500000`. Integers avoid floating-point rounding errors, and Paystack expects
kobo anyway.

### Overselling is blocked twice

1. **In the application**: booking runs inside a database transaction with
   `lockForUpdate()` on the event row. Two simultaneous bookings for the last
   seat can't both succeed; the second waits, recounts, and is refused.
2. **In the database**: a `tickets_sold` column with a CHECK constraint means
   PostgreSQL itself rejects any write that would exceed capacity, even one
   that bypasses the application.

Both layers are covered by a concurrency test suite.

### Webhooks are not trusted blindly

- The Paystack signature (HMAC-SHA512) is verified against the raw request body.
- Each event is recorded in `processed_webhook_events` with a unique index, so
  a webhook delivered twice is processed once.
- A queued job calls Paystack's verify endpoint and checks the amount before
  confirming the booking, instead of trusting the webhook body.

### No N+1 queries on the event listing

`GET /events` runs 4 queries regardless of page size: one for the events, one
for the pagination count, and one each for categories and organizers, loaded
via `with(['category', 'organizer'])`. Without eager loading this would be
1 + 2N queries, which is 21 for a page of 10.

## Tests

```bash
php artisan test
```

Feature tests cover authentication, role and ownership checks, booking limits,
and concurrent bookings.