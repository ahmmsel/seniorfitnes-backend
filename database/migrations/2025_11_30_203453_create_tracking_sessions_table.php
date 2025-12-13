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
        Schema::create('tracking_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trainee_id')->constrained('trainee_profiles')->onDelete('cascade');
            $table->enum('status', ['ongoing', 'finished'])->default('ongoing');
            $table->float('distance')->nullable();
            $table->integer('time_seconds')->nullable();
            $table->integer('bpm')->nullable();
            $table->integer('steps')->nullable();
            $table->float('pace')->nullable();
            $table->float('calories')->nullable();
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tracking_sessions');
    }
};
