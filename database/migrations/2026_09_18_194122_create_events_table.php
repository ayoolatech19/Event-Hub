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
        Schema::create('events', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->cascadeOnDelete();      // the organizer
        $table->foreignId('category_id')->constrained()->restrictOnDelete();
        $table->string('title');
        $table->text('description');
        $table->string('venue');
        $table->dateTime('date');
        $table->unsignedInteger('capacity');
        $table->decimal('price', 10, 2)->default(0);
        $table->string('banner_image')->nullable();
        $table->enum('status', ['draft', 'published'])->default('draft');
        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
