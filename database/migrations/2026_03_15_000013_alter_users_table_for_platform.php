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
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->nullable()->unique()->after('id');
            $table->string('first_name')->nullable()->after('name');
            $table->string('last_name')->nullable()->after('first_name');
            $table->string('phone')->nullable()->after('last_name');
            $table->string('employee_no')->nullable()->unique()->after('phone');
            $table->foreignId('department_id')->nullable()->after('employee_no')->constrained('departments')->nullOnDelete();
            $table->boolean('is_active')->default(true)->after('department_id');
            $table->timestamp('last_login_at')->nullable()->after('is_active');
            $table->timestamp('password_changed_at')->nullable()->after('last_login_at');
            $table->unsignedInteger('failed_login_attempts')->default(0)->after('password_changed_at');
            $table->timestamp('locked_until')->nullable()->after('failed_login_attempts');
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropForeign(['department_id']);
            $table->dropColumn([
                'username',
                'first_name',
                'last_name',
                'phone',
                'employee_no',
                'department_id',
                'is_active',
                'last_login_at',
                'password_changed_at',
                'failed_login_attempts',
                'locked_until',
            ]);
        });
    }
};
