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
        // Remove duplicate parts before adding unique constraint
        // Keep the first occurrence of each part_no
        $duplicates = DB::table('parts')
            ->select('part_no', DB::raw('MIN(id) as min_id'))
            ->whereNotNull('part_no')
            ->groupBy('part_no')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicates as $duplicate) {
            DB::table('parts')
                ->where('part_no', $duplicate->part_no)
                ->where('id', '!=', $duplicate->min_id)
                ->delete();
        }

        // Add unique constraint to part_no
        Schema::table('parts', function (Blueprint $table) {
            $table->unique('part_no');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('parts', function (Blueprint $table) {
            $table->dropUnique(['part_no']);
        });
    }
};
