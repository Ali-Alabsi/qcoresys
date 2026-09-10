<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('portfolio_projects', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title');
            $table->string('title_ar')->nullable();
            $table->text('short_description')->nullable();
            $table->text('short_description_ar')->nullable();
            $table->text('description')->nullable();
            $table->text('description_ar')->nullable();
            $table->string('client_name')->nullable();
            $table->string('industry')->nullable();
            $table->string('industry_ar')->nullable();
            $table->string('featured_image')->nullable();
            $table->date('completion_date')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_active', 'is_featured', 'sort_order']);
        });

        Schema::create('portfolio_project_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('portfolio_project_id')->constrained('portfolio_projects')->cascadeOnDelete();
            $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['portfolio_project_id', 'service_id'], 'portfolio_service_unique');
        });

        Schema::create('portfolio_project_technologies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('portfolio_project_id')->constrained('portfolio_projects')->cascadeOnDelete();
            $table->foreignId('technology_id')->constrained('technologies')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['portfolio_project_id', 'technology_id'], 'portfolio_technology_unique');
        });

        Schema::create('portfolio_project_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('portfolio_project_id')->constrained('portfolio_projects')->cascadeOnDelete();
            $table->string('image_path');
            $table->string('alt_text')->nullable();
            $table->string('alt_text_ar')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['portfolio_project_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('portfolio_project_images');
        Schema::dropIfExists('portfolio_project_technologies');
        Schema::dropIfExists('portfolio_project_services');
        Schema::dropIfExists('portfolio_projects');
    }
};
