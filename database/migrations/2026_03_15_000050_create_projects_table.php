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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('project_no')->unique();
            $table->foreignId('customer_id')->constrained('customers')->restrictOnDelete();
            $table->foreignId('quotation_id')->nullable()->constrained('quotations')->nullOnDelete();
            $table->foreignId('contract_id')->nullable()->constrained('contracts')->nullOnDelete();
            $table->foreignId('request_id')->nullable()->constrained('customer_requests')->nullOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('project_type');
            $table->string('status');
            $table->string('priority');
            $table->foreignId('project_manager_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('start_date')->nullable();
            $table->date('expected_end_date')->nullable();
            $table->date('actual_end_date')->nullable();
            $table->decimal('budget_amount', 18, 2)->default(0);
            $table->decimal('contract_amount', 18, 2)->default(0);
            $table->foreignId('currency_id')->constrained('currencies')->restrictOnDelete();
            $table->decimal('estimated_cost', 18, 2)->default(0);
            $table->decimal('actual_cost', 18, 2)->default(0);
            $table->decimal('estimated_revenue', 18, 2)->default(0);
            $table->decimal('actual_revenue', 18, 2)->default(0);
            $table->decimal('estimated_profit', 18, 2)->default(0);
            $table->decimal('actual_profit', 18, 2)->default(0);
            $table->decimal('completion_percentage', 8, 4)->default(0);
            $table->string('billing_type')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['customer_id', 'status']);
            $table->index(['customer_id', 'created_at']);
            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
