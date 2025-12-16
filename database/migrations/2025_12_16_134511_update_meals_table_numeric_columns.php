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
        Schema::table('meals', function (Blueprint $table) {
            $table->decimal('calories', 8, 2)->nullable()->change();
            $table->decimal('protein', 8, 2)->nullable()->change();
            $table->decimal('carbs', 8, 2)->nullable()->change();
            $table->decimal('fats', 8, 2)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('meals', function (Blueprint $table) {
            $table->decimal('calories', 6, 4)->change();
            $table->decimal('protein', 4, 4)->change();
            $table->decimal('carbs', 4, 4)->change();
            $table->decimal('fats', 4, 4)->change();
        });
    }
};
