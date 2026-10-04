<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_details', function (Blueprint $table) {
            $table->json('custom_services')->nullable()->after('service_types');
        });
    }

    public function down(): void
    {
        Schema::table('service_details', function (Blueprint $table) {
            $table->dropColumn('custom_services');
        });
    }
};
