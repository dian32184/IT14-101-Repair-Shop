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
    Schema::table('receipts', function (Blueprint $table) {
        $table->foreignId('transaction_id')->nullable()->constrained()->cascadeOnDelete()->after('id');
    });
}

public function down(): void
{
    Schema::table('receipts', function (Blueprint $table) {
        $table->dropConstrainedForeignId('transaction_id');
    });
}
};
