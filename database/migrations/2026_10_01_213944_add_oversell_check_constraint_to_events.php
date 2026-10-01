<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
{
    DB::statement('ALTER TABLE events ADD CONSTRAINT tickets_not_oversold CHECK (tickets_sold <= capacity)');
}

public function down(): void
{
    DB::statement('ALTER TABLE events DROP CONSTRAINT tickets_not_oversold');
}
};
