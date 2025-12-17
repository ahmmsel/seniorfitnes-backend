<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update existing rows with the typo to the correct value
        DB::table('trainee_profiles')
            ->where('goal', 'lose_wight')
            ->update(['goal' => 'lose_weight']);

        // Modify the enum column to use the corrected values
        // Using raw SQL because Laravel doesn't support enum modification directly
        DB::statement("ALTER TABLE trainee_profiles MODIFY COLUMN goal ENUM('lose_weight', 'build_muscle', 'improve_cardio', 'maintain_fitness') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to original typo if needed
        DB::table('trainee_profiles')
            ->where('goal', 'lose_weight')
            ->update(['goal' => 'lose_wight']);

        DB::statement("ALTER TABLE trainee_profiles MODIFY COLUMN goal ENUM('lose_wight', 'build_muscle', 'improve_cardio', 'maintain_fitness') NOT NULL");
    }
};
