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
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('service_code')->unique();
            $table->foreignId('category_id')->constrained('service_categories')->restrictOnDelete();
            $table->string('name');
            $table->string('short_name')->nullable();
            $table->text('description')->nullable();
            $table->string('service_type');
            $table->string('billing_type');
            $table->string('unit');
            $table->decimal('default_quantity', 18, 4)->default(1);
            $table->decimal('default_price', 18, 2)->default(0);
            $table->decimal('cost_price', 18, 2)->default(0);
            $table->foreignId('currency_id')->constrained('currencies')->restrictOnDelete();
            $table->decimal('tax_rate', 8, 4)->default(0);
            $table->decimal('estimated_hours', 18, 4)->nullable();
            $table->boolean('is_consulting')->default(false);
            $table->boolean('is_project_service')->default(false);
            $table->boolean('is_recurring')->default(false);
            $table->string('recurring_period')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
