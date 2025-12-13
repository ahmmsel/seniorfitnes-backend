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
        Schema::create('trainee_profiles', function (Blueprint $table) {
            $table->id();
            $table
                ->foreignId('user_id')
                ->unique()
                ->constrained('users')
                ->onDelete('cascade');
            $table->decimal('height');
            $table->decimal('weight');
            $table->enum('goal', ['lose_wight', 'build_muscle', 'improve_cardio', 'maintain_fitness']);
            $table->enum('level', ['sedentary', 'lightly_active', 'active', 'very_active']);
            $table->enum('body_type', ['underweight', 'normal', 'overweight', 'obese']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trainee_profiles');
    }
};
