<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
{
    DB::statement('ALTER TABLE bookings DROP CONSTRAINT IF EXISTS bookings_status_check');
    DB::statement("ALTER TABLE bookings ADD CONSTRAINT bookings_status_check CHECK (status IN ('pending', 'confirmed', 'cancelled', 'expired'))");
    DB::statement("ALTER TABLE bookings ALTER COLUMN status SET DEFAULT 'pending'");
}

public function down(): void
{
    DB::statement('ALTER TABLE bookings DROP CONSTRAINT IF EXISTS bookings_status_check');
    DB::statement("ALTER TABLE bookings ADD CONSTRAINT bookings_status_check CHECK (status IN ('confirmed', 'cancelled'))");
    DB::statement("ALTER TABLE bookings ALTER COLUMN status SET DEFAULT 'confirmed'");
}
};
