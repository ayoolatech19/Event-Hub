## 2. How did you prove overselling is impossible?

Two independent layers.

**Application:** `BookingController@store` opens a transaction and re-reads the
event row with `lockForUpdate()` before counting. Concurrent bookings are
serialised, so the second request reads the count *after* the first one wrote
it rather than a stale snapshot. The counter is incremented inside the same
transaction.

**Database:** `events` carries `CHECK (tickets_sold <= capacity)`. This holds
regardless of what the application does.

`tests/Feature/ConcurrencyTest.php` covers both. The first test asserts that
with capacity 1 and ten booking attempts, exactly one succeeds. The second
bypasses the application entirely and writes `tickets_sold = 6` on a
capacity-5 event via the query builder, asserting the database rejects it.