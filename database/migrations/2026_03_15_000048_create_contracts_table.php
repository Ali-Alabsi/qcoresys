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
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->string('contract_no')->unique();
            $table->foreignId('customer_id')->constrained('customers')->restrictOnDelete();
            $table->foreignId('quotation_id')->nullable()->constrained('quotations')->nullOnDelete();
            $table->foreignId('proposal_id')->nullable()->constrained('proposals')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('contract_type');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->date('signed_date')->nullable();
            $table->decimal('contract_amount', 18, 2)->default(0);
            $table->foreignId('currency_id')->constrained('currencies')->restrictOnDelete();
            $table->text('payment_terms')->nullable();
            $table->string('status');
            $table->boolean('auto_renew')->default(false);
            $table->string('renewal_period')->nullable();
            $table->timestamp('terminated_at')->nullable();
            $table->text('termination_reason')->nullable();
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
        Schema::dropIfExists('contracts');
    }
};
