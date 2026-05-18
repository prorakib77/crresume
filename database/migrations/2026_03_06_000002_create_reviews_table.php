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
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->string('customer_name', 120);
            $table->string('country_label', 16)->nullable();
            $table->string('headline', 190);
            $table->text('review_text');
            $table->string('product_name', 190)->nullable();
            $table->string('product_link', 2048)->nullable();
            $table->string('before_image_path')->nullable();
            $table->string('before_image_url', 2048)->nullable();
            $table->string('after_image_path')->nullable();
            $table->string('after_image_url', 2048)->nullable();
            $table->boolean('is_verified')->default(true);
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
