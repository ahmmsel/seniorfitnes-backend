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
        Schema::create('targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trainee_profile_id')->constrained('trainee_profiles')->cascadeOnDelete();
            $table->integer('target_calories');
            $table->integer('target_steps');
            $table->decimal('target_water_liters', 5, 2);
            $table->unique(['trainee_profile_id', 'target_date']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_targets');
    }
};
