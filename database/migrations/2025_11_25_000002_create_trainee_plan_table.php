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
        Schema::create('trainee_plan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trainee_id')->constrained('trainee_profiles')->onDelete('cascade');
            // optional reference to plans table if coach created a Plan model
            $table->foreignId('plan_id')->nullable()->constrained('plans')->nullOnDelete();
            // or reference to coach profile + plan type when using coach_profile-level plans
            $table->foreignId('coach_profile_id')->nullable()->constrained('coach_profiles')->nullOnDelete();
            $table->string('plan_type')->nullable();
            // store selected workout/meal ids or other metadata
            $table->json('items')->nullable();
            $table->string('tap_charge_id')->nullable();
            $table->timestamp('purchased_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trainee_plan');
    }
};
