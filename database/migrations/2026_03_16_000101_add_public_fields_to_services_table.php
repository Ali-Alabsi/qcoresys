<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->string('slug')->nullable()->unique()->after('service_code');
            $table->string('name_ar')->nullable()->after('name');
            $table->text('short_description')->nullable()->after('short_name');
            $table->text('short_description_ar')->nullable()->after('short_description');
            $table->text('description_ar')->nullable()->after('description');
            $table->text('overview')->nullable()->after('description_ar');
            $table->text('overview_ar')->nullable()->after('overview');
            $table->string('icon')->nullable()->after('overview_ar');
            $table->string('image')->nullable()->after('icon');
            $table->string('banner_image')->nullable()->after('image');
            $table->string('pricing_type')->nullable()->after('billing_type');
            $table->decimal('starting_price', 18, 2)->nullable()->after('default_price');
            $table->boolean('is_public')->default(false)->after('is_active');
            $table->boolean('is_featured')->default(false)->after('is_public');
            $table->unsignedInteger('sort_order')->default(0)->after('is_featured');
            $table->string('meta_title')->nullable()->after('notes');
            $table->string('meta_title_ar')->nullable()->after('meta_title');
            $table->text('meta_description')->nullable()->after('meta_title_ar');
            $table->text('meta_description_ar')->nullable()->after('meta_description');

            $table->index(['is_active', 'is_public']);
            $table->index('is_featured');
            $table->index('pricing_type');
            $table->index('service_type');
            $table->index('sort_order');
        });
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropIndex(['is_active', 'is_public']);
            $table->dropIndex(['is_featured']);
            $table->dropIndex(['pricing_type']);
            $table->dropIndex(['service_type']);
            $table->dropIndex(['sort_order']);
            $table->dropColumn([
                'slug',
                'name_ar',
                'short_description',
                'short_description_ar',
                'description_ar',
                'overview',
                'overview_ar',
                'icon',
                'image',
                'banner_image',
                'pricing_type',
                'starting_price',
                'is_public',
                'is_featured',
                'sort_order',
                'meta_title',
                'meta_title_ar',
                'meta_description',
                'meta_description_ar',
            ]);
        });
    }
};
