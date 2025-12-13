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
        Schema::create('targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trainee_profile_id')->constrained('trainee_profiles')->cascadeOnDelete();
            $table->integer('target_calories');
            $table->integer('target_steps');
            $table->decimal('target_water_liters', 5, 2);
            // Date for the target (one per day per trainee)
            // Use a NOT NULL column with a sensible default (current date).
            $table->date('target_date')->default(DB::raw('CURRENT_DATE'));
            // Unique per trainee per date
            $table->unique(['trainee_profile_id', 'target_date'], 'targets_trainee_date_uq');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('targets');
    }
};
