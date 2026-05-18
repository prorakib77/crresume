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
        Schema::create('mailchimp_settings', function (Blueprint $table) {
            $table->id();
            $table->string('api_key')->nullable();
            $table->string('server_prefix')->default('us18');
            $table->string('list_id')->nullable();
            $table->string('from_name')->default('W-Automation');
            $table->string('from_email')->nullable();
            $table->string('reply_to')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('auto_subscribe')->default(true);
            $table->boolean('send_welcome_email')->default(true);
            $table->text('welcome_email_template')->nullable();
            $table->text('work_update_template')->nullable();
            $table->json('merge_fields')->nullable();
            $table->json('tags')->nullable();
            $table->timestamp('last_sync_at')->nullable();
            $table->text('sync_status')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mailchimp_settings');
    }
};
