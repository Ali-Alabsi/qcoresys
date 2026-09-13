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
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->string('account_code')->unique();
            $table->string('account_name');
            $table->string('account_type');
            $table->foreignId('parent_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->unsignedTinyInteger('account_level')->default(1);
            $table->string('normal_balance');
            $table->foreignId('currency_id')->nullable()->constrained('currencies')->nullOnDelete();
            $table->boolean('is_control_account')->default(false);
            $table->boolean('is_cash_account')->default(false);
            $table->boolean('is_bank_account')->default(false);
            $table->boolean('is_customer_account')->default(false);
            $table->boolean('is_vendor_account')->default(false);
            $table->boolean('allow_posting')->default(true);
            $table->decimal('opening_balance', 18, 2)->default(0);
            $table->decimal('opening_debit', 18, 2)->default(0);
            $table->decimal('opening_credit', 18, 2)->default(0);
            $table->decimal('current_balance', 18, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
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
        Schema::dropIfExists('accounts');
    }
};
