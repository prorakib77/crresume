<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            return;
        }

        $this->syncNotificationsTable();
        $this->syncOtpSubmissionsTable();
    }

    public function down(): void
    {
        // Intentionally left blank. This migration only normalizes SQLite
        // constraints to match the application's current supported enums.
    }

    private function syncNotificationsTable(): void
    {
        if (!Schema::hasTable('notifications') || $this->tableSqlContains('notifications', 'otp_submission')) {
            return;
        }

        DB::statement('PRAGMA foreign_keys = OFF');

        Schema::create('notifications_sqlite_rebuild', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('message');
            $table->enum('type', [
                'info',
                'success',
                'warning',
                'error',
                'work_update',
                'approval',
                'rejection',
                'system',
                'otp_request',
                'otp_submission',
            ])->default('info');
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            $table->json('data')->nullable();
            $table->string('notifiable_type')->nullable();
            $table->unsignedBigInteger('notifiable_id')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->string('action_url')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        DB::statement(<<<'SQL'
            INSERT INTO notifications_sqlite_rebuild (
                id, user_id, title, message, type, priority, data, notifiable_type,
                notifiable_id, read_at, action_url, expires_at, created_at, updated_at, deleted_at
            )
            SELECT
                id, user_id, title, message, type, priority, data, notifiable_type,
                notifiable_id, read_at, action_url, expires_at, created_at, updated_at, deleted_at
            FROM notifications
        SQL);

        Schema::drop('notifications');
        Schema::rename('notifications_sqlite_rebuild', 'notifications');

        Schema::table('notifications', function (Blueprint $table) {
            $table->index(['user_id', 'read_at']);
            $table->index(['user_id', 'type']);
            $table->index(['user_id', 'priority']);
            $table->index(['notifiable_type', 'notifiable_id']);
            $table->index('expires_at');
            $table->index('created_at');
        });

        DB::statement('PRAGMA foreign_keys = ON');
    }

    private function syncOtpSubmissionsTable(): void
    {
        if (!Schema::hasTable('otp_submissions') || $this->tableSqlContains('otp_submissions', 'processed')) {
            return;
        }

        DB::statement('PRAGMA foreign_keys = OFF');

        Schema::create('otp_submissions_sqlite_rebuild', function (Blueprint $table) {
            $table->id();
            $table->foreignId('otp_verification_id')->constrained('otp_verifications')->onDelete('cascade');
            $table->foreignId('agent_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('client_id')->constrained('users')->onDelete('cascade');
            $table->string('company_name');
            $table->string('otp_code');
            $table->enum('status', ['pending', 'reviewed', 'approved', 'processed', 'rejected'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamp('submitted_at');
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });

        DB::statement(<<<'SQL'
            INSERT INTO otp_submissions_sqlite_rebuild (
                id, otp_verification_id, agent_id, client_id, company_name,
                otp_code, status, notes, submitted_at, reviewed_at, created_at, updated_at
            )
            SELECT
                id, otp_verification_id, agent_id, client_id, company_name,
                otp_code, status, notes, submitted_at, reviewed_at, created_at, updated_at
            FROM otp_submissions
        SQL);

        Schema::drop('otp_submissions');
        Schema::rename('otp_submissions_sqlite_rebuild', 'otp_submissions');

        DB::statement('PRAGMA foreign_keys = ON');
    }

    private function tableSqlContains(string $table, string $needle): bool
    {
        $row = DB::table('sqlite_master')
            ->select('sql')
            ->where('type', 'table')
            ->where('name', $table)
            ->first();

        return str_contains((string) ($row->sql ?? ''), $needle);
    }
};
