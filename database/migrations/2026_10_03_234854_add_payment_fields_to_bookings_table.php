<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
public function up(): void
{
    Schema::table('bookings', function (Blueprint $table) {
        $table->string('reference', 64)->unique()->nullable()->after('id');
        $table->timestamp('paid_at')->nullable();
        $table->timestamp('expires_at')->nullable();
    });
}

public function down(): void
{
    Schema::table('bookings', function (Blueprint $table) {
        $table->dropColumn(['reference', 'paid_at', 'expires_at']);
    });
}
};
