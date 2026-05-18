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
        Schema::table('work_updates', function (Blueprint $table) {
            $table->text('job_link')->nullable()->change();
            $table->text('job_success_link')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('work_updates', function (Blueprint $table) {
            $table->string('job_link', 255)->nullable()->change();
            $table->string('job_success_link', 255)->nullable()->change();
        });
    }
};
