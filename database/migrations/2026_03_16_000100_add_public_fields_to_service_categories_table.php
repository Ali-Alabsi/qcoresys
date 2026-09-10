<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_categories', function (Blueprint $table) {
            $table->string('slug')->nullable()->unique()->after('code');
            $table->string('name_ar')->nullable()->after('name');
            $table->text('description_ar')->nullable()->after('description');
            $table->string('icon')->nullable()->after('description_ar');
            $table->boolean('is_public')->default(false)->after('is_active');

            $table->index(['is_active', 'is_public']);
        });
    }

    public function down(): void
    {
        Schema::table('service_categories', function (Blueprint $table) {
            $table->dropIndex(['is_active', 'is_public']);
            $table->dropColumn([
                'slug',
                'name_ar',
                'description_ar',
                'icon',
                'is_public',
            ]);
        });
    }
};
