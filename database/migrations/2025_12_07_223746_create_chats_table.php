<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('chats');
        Schema::dropIfExists('chat_user');
        Schema::enableForeignKeyConstraints();

        Schema::create('chats', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('coach_id');
            $table->unsignedBigInteger('trainee_id');
            $table->text('last_message')->nullable();
            $table->timestamp('last_message_at')->nullable();
            $table->timestamps();

            $table->foreign('coach_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('trainee_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique(['coach_id', 'trainee_id'], 'chats_coach_trainee_unique');

            $table->index(['coach_id', 'updated_at'], 'chats_coach_updated_index');
            $table->index(['trainee_id', 'updated_at'], 'chats_trainee_updated_index');
        });
    }

    public function down()
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('chats');
        Schema::enableForeignKeyConstraints();

        Schema::create('chats', function (Blueprint $table) {});
    }
};
