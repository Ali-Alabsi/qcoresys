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
        Schema::create('project_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('service_id')->nullable()->constrained('services')->nullOnDelete();
            $table->text('description')->nullable();
            $table->decimal('quantity', 18, 4)->default(1);
            $table->string('unit')->nullable();
            $table->decimal('unit_price', 18, 2)->default(0);
            $table->decimal('total_amount', 18, 2)->default(0);
            $table->decimal('estimated_hours', 18, 4)->default(0);
            $table->decimal('actual_hours', 18, 4)->default(0);
            $table->decimal('estimated_cost', 18, 2)->default(0);
            $table->decimal('actual_cost', 18, 2)->default(0);
            $table->decimal('estimated_profit', 18, 2)->default(0);
            $table->decimal('actual_profit', 18, 2)->default(0);
            $table->string('status');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['project_id', 'status']);
            $table->index(['project_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_services');
    }
};
