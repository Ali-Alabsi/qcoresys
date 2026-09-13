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
        Schema::create('exchange_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('from_currency_id')->constrained('currencies')->restrictOnDelete();
            $table->foreignId('to_currency_id')->constrained('currencies')->restrictOnDelete();
            $table->decimal('rate', 20, 10);
            $table->date('rate_date');
            $table->string('rate_type');
            $table->string('source')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['from_currency_id', 'to_currency_id', 'rate_date']);
            $table->index(['from_currency_id', 'to_currency_id', 'rate_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exchange_rates');
    }
};
