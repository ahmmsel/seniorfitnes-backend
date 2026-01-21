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
        Schema::table('tracking_sessions', function (Blueprint $table) {
            $table->enum('type', ['walking', 'running'])->after('trainee_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tracking_sessions', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
