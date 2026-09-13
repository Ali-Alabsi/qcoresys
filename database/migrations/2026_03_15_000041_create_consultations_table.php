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
        Schema::create('consultations', function (Blueprint $table) {
            $table->id();
            $table->string('consultation_no')->unique();
            $table->foreignId('customer_id')->constrained('customers')->restrictOnDelete();
            $table->foreignId('contact_id')->nullable()->constrained('customer_contacts')->nullOnDelete();
            $table->foreignId('request_id')->nullable()->constrained('customer_requests')->nullOnDelete();
            $table->foreignId('consultant_id')->constrained('users')->restrictOnDelete();
            $table->string('consultation_type');
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('objectives')->nullable();
            $table->text('findings')->nullable();
            $table->text('recommendations')->nullable();
            $table->text('technical_requirements')->nullable();
            $table->text('risks')->nullable();
            $table->decimal('estimated_effort_hours', 18, 4)->nullable();
            $table->date('consultation_date');
            $table->date('follow_up_date')->nullable();
            $table->string('status');
            $table->boolean('billable')->default(false);
            $table->decimal('amount', 18, 2)->nullable();
            $table->foreignId('currency_id')->nullable()->constrained('currencies')->nullOnDelete();
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
        Schema::dropIfExists('consultations');
    }
};
