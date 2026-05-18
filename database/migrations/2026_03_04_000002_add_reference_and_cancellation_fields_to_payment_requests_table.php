<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_requests', function (Blueprint $table) {
            $table->unsignedInteger('reference_number')->nullable()->after('id')->unique();
            $table->foreignId('cancelled_by')->nullable()->after('rejected_by')->constrained('users')->nullOnDelete();
            $table->timestamp('cancelled_at')->nullable()->after('rejected_at');
            $table->text('cancellation_reason')->nullable()->after('rejection_reason');
        });

        $usedReferences = DB::table('payment_requests')
            ->whereNotNull('reference_number')
            ->pluck('reference_number')
            ->map(fn ($reference) => (int) $reference)
            ->all();

        DB::table('payment_requests')
            ->select('id')
            ->orderBy('id')
            ->chunkById(100, function ($paymentRequests) use (&$usedReferences) {
                foreach ($paymentRequests as $paymentRequest) {
                    $referenceNumber = $this->generateUniqueReferenceNumber($usedReferences);

                    DB::table('payment_requests')
                        ->where('id', $paymentRequest->id)
                        ->update(['reference_number' => $referenceNumber]);

                    $usedReferences[] = $referenceNumber;
                }
            });
    }

    public function down(): void
    {
        Schema::table('payment_requests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('cancelled_by');
            $table->dropColumn(['reference_number', 'cancelled_at', 'cancellation_reason']);
        });
    }

    private function generateUniqueReferenceNumber(array $usedReferences): int
    {
        do {
            $referenceNumber = random_int(100000, 999999);
        } while (in_array($referenceNumber, $usedReferences, true));

        return $referenceNumber;
    }
};
