<?php

use Illuminate\Auth\Events\Validated;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Client validation rules should be placed in a FormRequest or Controller, not in a migration.
        Schema::create('client_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->string('apply_to')->nullable();
            $table->integer('status')->nullable();
            $table->string('resume')->nullable(); // path to file
            $table->string('onboarding_file')->nullable(); // path to file (doc, excel, pdf)
            $table->date('service_start_date')->nullable();
            $table->date('service_end_date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_profiles');
    }
};
