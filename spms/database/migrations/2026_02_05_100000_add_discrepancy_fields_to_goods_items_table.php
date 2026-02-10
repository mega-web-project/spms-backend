<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('goods_items', function (Blueprint $table) {
            $table->boolean('has_discrepancy')->default(false)->after('reference_doc');
            $table->text('discrepancy_note')->nullable()->after('has_discrepancy');
        });

        // Backfill legacy visit-level discrepancies onto the first goods item.
        $visitIds = DB::table('visits')
            ->where('has_discrepancies', true)
            ->pluck('id');

        foreach ($visitIds as $visitId) {
            $itemId = DB::table('goods_items')
                ->where('visit_id', $visitId)
                ->orderBy('id')
                ->value('id');

            if ($itemId) {
                DB::table('goods_items')
                    ->where('id', $itemId)
                    ->update([
                        'has_discrepancy' => true,
                        'discrepancy_note' => 'Legacy visit-level discrepancy',
                        'updated_at' => now(),
                    ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('goods_items', function (Blueprint $table) {
            $table->dropColumn(['has_discrepancy', 'discrepancy_note']);
        });
    }
};
