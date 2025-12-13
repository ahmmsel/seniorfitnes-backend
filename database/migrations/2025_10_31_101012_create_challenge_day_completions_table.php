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
        Schema::create('challenge_day_completions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('challenge_id')->constrained('challenges')->cascadeOnDelete();
            $table->foreignId('trainee_id')->constrained('trainee_profiles')->cascadeOnDelete();
            $table->unsignedInteger('day_number');
            $table->timestamps();

            // MySQL limits index identifier length to 64 chars. Provide a short name to avoid
            // "Identifier name too long" errors on some installations.
            $table->unique(['challenge_id', 'trainee_id', 'day_number'], 'cdc_challenge_trainee_day_uq');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('challenge_day_completions');
    }
};
