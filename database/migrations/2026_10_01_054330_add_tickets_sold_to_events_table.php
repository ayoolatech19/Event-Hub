<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
  public function up(): void
{
    Schema::table('events', function (Blueprint $table) {
        $table->unsignedInteger('tickets_sold')->default(0)->after('capacity');
    });

    // backfill from existing confirmed bookings
    DB::statement("
        UPDATE events
        SET tickets_sold = COALESCE((
            SELECT SUM(quantity)
            FROM bookings
            WHERE bookings.event_id = events.id
              AND bookings.status = 'confirmed'
        ), 0)
    ");
}

public function down(): void
{
    Schema::table('events', function (Blueprint $table) {
        $table->dropColumn('tickets_sold');
    });
}
};
