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
        Schema::create('appliance_problems', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appliance_id')->constrained('appliances')->onDelete('cascade');
            $table->foreignId('common_problem_id')->nullable()->constrained('common_problems')->onDelete('set null');
            $table->text('other_problem')->nullable(); // For custom problems not in the list
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appliance_problems');
    }
};
