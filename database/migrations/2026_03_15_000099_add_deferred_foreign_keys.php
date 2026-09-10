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
        Schema::table('customer_requests', function (Blueprint $table) {
            $table->foreign('quotation_id')
                ->references('id')
                ->on('quotations')
                ->nullOnDelete();

            $table->foreign('project_id')
                ->references('id')
                ->on('projects')
                ->nullOnDelete();
        });

        Schema::table('departments', function (Blueprint $table) {
            $table->foreign('manager_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });

        Schema::table('project_costs', function (Blueprint $table) {
            $table->foreign('expense_id')
                ->references('id')
                ->on('expenses')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_costs', function (Blueprint $table) {
            $table->dropForeign(['expense_id']);
        });

        Schema::table('departments', function (Blueprint $table) {
            $table->dropForeign(['manager_id']);
        });

        Schema::table('customer_requests', function (Blueprint $table) {
            $table->dropForeign(['quotation_id']);
            $table->dropForeign(['project_id']);
        });
    }
};
