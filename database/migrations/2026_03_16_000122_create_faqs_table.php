<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('faqs', function (Blueprint $table) {
            $table->id();
            $table->string('question');
            $table->string('question_ar')->nullable();
            $table->text('answer');
            $table->text('answer_ar')->nullable();
            $table->string('category')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_active', 'is_featured', 'sort_order']);
        });

        Schema::create('service_faqs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
            $table->foreignId('faq_id')->constrained('faqs')->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['service_id', 'faq_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_faqs');
        Schema::dropIfExists('faqs');
    }
};
