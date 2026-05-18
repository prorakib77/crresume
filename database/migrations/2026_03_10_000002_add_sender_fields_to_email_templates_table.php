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
        Schema::table('email_templates', function (Blueprint $table): void {
            $table->string('from_name', 190)->nullable()->after('body_template');
            $table->string('from_email', 255)->nullable()->after('from_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('email_templates', function (Blueprint $table): void {
            $table->dropColumn(['from_name', 'from_email']);
        });
    }
};
