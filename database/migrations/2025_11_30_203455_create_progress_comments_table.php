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
        Schema::create('progress_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained('progress_posts')->onDelete('cascade');
            $table->foreignId('trainee_id')->constrained('trainee_profiles')->onDelete('cascade');
            $table->text('comment');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('progress_comments');
    }
};
