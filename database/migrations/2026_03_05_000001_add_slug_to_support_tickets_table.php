<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('support_tickets', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('subject')->unique();
        });

        $tickets = DB::table('support_tickets')
            ->select('id', 'subject')
            ->orderBy('id')
            ->get();

        foreach ($tickets as $ticket) {
            DB::table('support_tickets')
                ->where('id', $ticket->id)
                ->update([
                    'slug' => $this->makeUniqueSlug((string) $ticket->subject, (int) $ticket->id),
                ]);
        }
    }

    public function down(): void
    {
        Schema::table('support_tickets', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn('slug');
        });
    }

    protected function makeUniqueSlug(string $subject, int $ignoreId): string
    {
        $baseSlug = Str::slug(Str::limit(trim($subject), 120, ''));
        $baseSlug = $baseSlug !== '' ? $baseSlug : 'support-ticket';
        $slug = $baseSlug;
        $suffix = 2;

        while (
            DB::table('support_tickets')
                ->where('id', '!=', $ignoreId)
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = $baseSlug . '-' . $suffix;
            $suffix++;
        }

        return $slug;
    }
};
