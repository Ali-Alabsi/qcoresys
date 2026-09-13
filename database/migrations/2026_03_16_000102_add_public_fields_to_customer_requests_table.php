<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customer_requests', function (Blueprint $table) {
            $table->foreignId('service_id')->nullable()->after('contact_id')->constrained('services')->nullOnDelete();
            $table->string('project_type')->nullable()->after('requirements');
            $table->string('expected_timeline')->nullable()->after('requested_end_date');
            $table->string('preferred_contact_method')->nullable()->after('expected_timeline');
            $table->text('additional_notes')->nullable()->after('preferred_contact_method');
        });
    }

    public function down(): void
    {
        Schema::table('customer_requests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('service_id');
            $table->dropColumn([
                'project_type',
                'expected_timeline',
                'preferred_contact_method',
                'additional_notes',
            ]);
        });
    }
};
