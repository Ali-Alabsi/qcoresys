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
        Schema::create('customer_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_no')->unique();
            $table->foreignId('customer_id')->constrained('customers')->restrictOnDelete();
            $table->foreignId('contact_id')->nullable()->constrained('customer_contacts')->nullOnDelete();
            $table->string('request_type');
            $table->string('subject');
            $table->text('description')->nullable();
            $table->text('requirements')->nullable();
            $table->string('priority');
            $table->string('source')->nullable();
            $table->dateTime('received_at');
            $table->date('requested_start_date')->nullable();
            $table->date('requested_end_date')->nullable();
            $table->decimal('estimated_budget', 18, 2)->nullable();
            $table->foreignId('currency_id')->nullable()->constrained('currencies')->nullOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status');
            $table->boolean('consultation_required')->default(false);
            $table->boolean('quotation_required')->default(false);
            $table->unsignedBigInteger('quotation_id')->nullable();
            $table->unsignedBigInteger('project_id')->nullable();
            $table->string('closed_reason')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['customer_id', 'status']);
            $table->index(['customer_id', 'created_at']);
            $table->index(['assigned_to', 'status']);
            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_requests');
    }
};
