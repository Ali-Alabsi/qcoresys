<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('exchange_rates');
    }

    public function down(): void
    {
        // Intentionally empty: exchange rates feature was removed.
    }
};
