<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_requests', function (Blueprint $table) {
            $table->string('payment_proof_path')->nullable()->after('note');
            $table->timestamp('payment_proof_uploaded_at')->nullable()->after('client_marked_at');
        });
    }

    public function down(): void
    {
        Schema::table('payment_requests', function (Blueprint $table) {
            $table->dropColumn(['payment_proof_path', 'payment_proof_uploaded_at']);
        });
    }
};
