<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('common_problems', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appliance_type_id')->constrained('appliance_types')->onDelete('cascade');
            $table->string('problem_name'); // e.g., No Power, Screen Issues, Not Cooling
            $table->timestamps();
            
            $table->unique(['appliance_type_id', 'problem_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('common_problems');
    }
};
