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
        Schema::create('client_sales_popups', function (Blueprint $table): void {
            $table->id();
            $table->string('title', 190);
            $table->string('badge_text', 120)->nullable();
            $table->text('message')->nullable();
            $table->string('price_text', 80)->nullable();
            $table->string('cta_label', 50)->default('Book Now');
            $table->string('cta_link', 2048)->nullable();
            $table->string('image_path')->nullable();
            $table->string('image_url', 2048)->nullable();
            $table->string('bg_color', 7)->default('#111111');
            $table->string('text_color', 7)->default('#FFFFFF');
            $table->string('accent_color', 7)->default('#C8A45D');
            $table->string('target_type', 20)->default('recurring')->index();
            $table->unsignedBigInteger('target_client_id')->nullable()->index();
            $table->unsignedTinyInteger('show_delay')->default(1);
            $table->dateTime('starts_at')->nullable()->index();
            $table->dateTime('ends_at')->nullable()->index();
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
        Schema::dropIfExists('client_sales_popups');
    }
};
