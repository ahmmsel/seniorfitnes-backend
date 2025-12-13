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
        Schema::create('meals', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->longText('description')->nullable();
            $table->dateTime('date')->nullable();
            $table->enum('type', ['breakfast', 'lunch', 'dinner', 'snack', 'other']);
            $table->decimal('calories', 6, 4);
            $table->decimal('protein', 4, 4);
            $table->decimal('carbs', 4, 4);
            $table->decimal('fats', 4, 4);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meals');
    }
};
