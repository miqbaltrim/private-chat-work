<?php
// database/migrations/2024_01_01_000001_create_chat_tables.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Users table (replace default)
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username')->unique();
            $table->string('display_name');
            $table->string('password');
            $table->string('avatar')->nullable();
            $table->text('public_key')->nullable(); // E2E encryption
            $table->boolean('is_online')->default(false);
            $table->timestamp('last_seen')->nullable();
            $table->timestamps();
        });

        // Chat rooms
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_private')->default(false);
            $table->string('password')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
            $table->foreign('created_by')->references('id')->on('users');
        });

        // Room members
        Schema::create('room_members', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('room_id');
            $table->unsignedBigInteger('user_id');
            $table->enum('role', ['admin', 'member'])->default('member');
            $table->timestamps();
            $table->foreign('room_id')->references('id')->on('rooms')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique(['room_id', 'user_id']);
        });

        // Messages
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('room_id');
            $table->unsignedBigInteger('user_id');
            $table->text('content'); // encrypted content
            $table->enum('type', ['text', 'file', 'image', 'system'])->default('text');
            $table->string('file_path')->nullable();
            $table->string('file_name')->nullable();
            $table->integer('file_size')->nullable();
            $table->text('iv')->nullable(); // initialization vector for E2E
            $table->timestamps();
            $table->foreign('room_id')->references('id')->on('rooms')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
        Schema::dropIfExists('room_members');
        Schema::dropIfExists('rooms');
        Schema::dropIfExists('users');
    }
};
