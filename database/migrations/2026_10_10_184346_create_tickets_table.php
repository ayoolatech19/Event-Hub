<?php

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
        Schema::create('tickets', function (Blueprint $table) {
         $table->id();
          $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
        $table->string('code', 64)->unique();
        $table->string('qr_path')->nullable();
        $table->string('status')->default('valid');
        $table->timestamp('checked_in_at')->nullable();
        $table->foreignId('checked_in_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
