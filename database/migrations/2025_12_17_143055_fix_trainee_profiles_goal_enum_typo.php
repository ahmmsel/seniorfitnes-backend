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

        // SQLite doesn't support MODIFY COLUMN, so we need to recreate the table
        if (DB::getDriverName() === 'sqlite') {
            // For SQLite, we'll just update the data. The enum constraint
            // will be enforced at the application level via validation
            return;
        }

        // For MySQL/PostgreSQL
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

        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        DB::statement("ALTER TABLE trainee_profiles MODIFY COLUMN goal ENUM('lose_wight', 'build_muscle', 'improve_cardio', 'maintain_fitness') NOT NULL");
    }
};
