<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('testimonials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->string('name');
            $table->string('name_ar')->nullable();
            $table->string('company_name')->nullable();
            $table->string('company_name_ar')->nullable();
            $table->string('position')->nullable();
            $table->string('position_ar')->nullable();
            $table->text('content');
            $table->text('content_ar')->nullable();
            $table->string('avatar')->nullable();
            $table->unsignedTinyInteger('rating')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_active', 'is_featured', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('testimonials');
    }
};
