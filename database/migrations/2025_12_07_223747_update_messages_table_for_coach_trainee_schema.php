<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::dropIfExists('messages');

        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('chat_id');
            $table->unsignedBigInteger('sender_id');
            $table->enum('sender_type', ['coach', 'trainee']);
            $table->text('body')->nullable();
            $table->json('data')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->foreign('chat_id')->references('id')->on('chats')->onDelete('cascade');
            $table->foreign('sender_id')->references('id')->on('users')->onDelete('cascade');

            $table->index(['chat_id', 'created_at'], 'messages_chat_created_index');
            $table->index(['sender_id', 'sender_type'], 'messages_sender_index');
            $table->index(['chat_id', 'read_at'], 'messages_chat_read_index');
        });
    }

    public function down()
    {
        Schema::dropIfExists('messages');
    }
};
