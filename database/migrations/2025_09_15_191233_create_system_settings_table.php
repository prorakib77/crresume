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
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('category', 50)->index(); // e.g., 'general', 'email', 'security'
            $table->string('key', 100)->index(); // e.g., 'app_name', 'smtp_host'
            $table->text('value')->nullable(); // JSON or string value
            $table->string('type', 20)->default('string'); // string, integer, boolean, json, array
            $table->text('description')->nullable();
            $table->boolean('is_public')->default(false); // Can be accessed by frontend
            $table->boolean('is_encrypted')->default(false); // Should be encrypted
            $table->timestamps();
            
            // Composite unique key
            $table->unique(['category', 'key']);
            
            // Indexes for better performance
            $table->index(['category', 'is_public']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }
};
