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
        Schema::create('quotation_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quotation_id')->constrained('quotations')->cascadeOnDelete();
            $table->foreignId('service_id')->nullable()->constrained('services')->nullOnDelete();
            $table->text('description');
            $table->decimal('quantity', 18, 4)->default(1);
            $table->string('unit')->nullable();
            $table->decimal('unit_price', 18, 2)->default(0);
            $table->decimal('cost_price', 18, 2)->default(0);
            $table->decimal('discount_percentage', 8, 4)->default(0);
            $table->decimal('discount_amount', 18, 2)->default(0);
            $table->decimal('tax_percentage', 8, 4)->default(0);
            $table->decimal('tax_amount', 18, 2)->default(0);
            $table->decimal('subtotal', 18, 2)->default(0);
            $table->decimal('total', 18, 2)->default(0);
            $table->decimal('estimated_cost', 18, 2)->default(0);
            $table->decimal('estimated_profit', 18, 2)->default(0);
            $table->unsignedInteger('sort_order')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotation_items');
    }
};
