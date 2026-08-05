<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_messages', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('recipient')->nullable();
            $table->string('topic')->nullable();
            $table->text('body');
            $table->string('email')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });

        Schema::create('registration_requests', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone');
            $table->string('email');
            $table->unsignedTinyInteger('age')->nullable();
            $table->string('level')->nullable();
            $table->string('source')->nullable();
            $table->string('status')->default('new')->index();
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registration_requests');
        Schema::dropIfExists('contact_messages');
    }
};
