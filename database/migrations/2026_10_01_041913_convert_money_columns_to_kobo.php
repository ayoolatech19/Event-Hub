x<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up(): void
{
       // widen the columns so the multiplication has room
    Schema::table('events', function (Blueprint $table) {
        $table->decimal('price', 20, 2)->default(0)->change();
    });

    Schema::table('bookings', function (Blueprint $table) {
        $table->decimal('total_price', 20, 2)->change();
    });
    DB::table('events')->update(['price' => DB::raw('price * 100')]);
    DB::table('bookings')->update(['total_price' => DB::raw('total_price * 100')]);

    Schema::table('events', function (Blueprint $table) {
        $table->unsignedBigInteger('price')->default(0)->change();
    });

    Schema::table('bookings', function (Blueprint $table) {
        $table->unsignedBigInteger('total_price')->change();
    });
}
};
