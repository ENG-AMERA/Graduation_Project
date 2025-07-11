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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->string('password');
            $table->string('firstname');
            $table->string('lastname');
            $table->integer('age');
            $table->string('location');
            $table->string('phone');
            $table->enum('gender', ['male', 'female']);
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->integer('points')->default(0);
            $table->string('photo')->nullable();
            $table->timestamps();
        });
      
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
