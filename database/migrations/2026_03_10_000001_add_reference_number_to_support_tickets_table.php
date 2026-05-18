<?php

use App\Models\SupportTicket;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('support_tickets', function (Blueprint $table) {
            $table->unsignedInteger('reference_number')->nullable()->after('id')->unique();
        });

        $usedReferences = DB::table('support_tickets')
            ->whereNotNull('reference_number')
            ->pluck('reference_number')
            ->map(static fn ($reference) => (int) $reference)
            ->all();

        $tickets = DB::table('support_tickets')
            ->select('id')
            ->whereNull('reference_number')
            ->orderBy('id')
            ->get();

        foreach ($tickets as $ticket) {
            $referenceNumber = $this->generateUniqueReferenceNumber($usedReferences);

            DB::table('support_tickets')
                ->where('id', $ticket->id)
                ->update(['reference_number' => $referenceNumber]);

            $usedReferences[] = $referenceNumber;
        }
    }

    public function down(): void
    {
        Schema::table('support_tickets', function (Blueprint $table) {
            $table->dropUnique(['reference_number']);
            $table->dropColumn('reference_number');
        });
    }

    private function generateUniqueReferenceNumber(array $usedReferences): int
    {
        do {
            $digits = random_int(5, 7);
            $minimum = 10 ** ($digits - 1);
            $maximum = (10 ** $digits) - 1;
            $referenceNumber = random_int($minimum, $maximum);
        } while (
            in_array($referenceNumber, $usedReferences, true)
            || SupportTicket::query()->where('reference_number', $referenceNumber)->exists()
        );

        return $referenceNumber;
    }
};
