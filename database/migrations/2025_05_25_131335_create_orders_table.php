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
       Schema::create('orders', function (Blueprint $table) {
        $table->id();
       $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
        $table->string('name_medicine')->nullable();
        $table->string('photo')->nullable();
        $table->float('length');
        $table->float('width');
        $table->string('type');
        $table->dateTime('time')->nullable();
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
