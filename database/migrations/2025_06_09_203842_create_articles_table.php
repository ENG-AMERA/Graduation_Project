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
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->string('topic');
            $table->mediumText('content');
            $table->foreignId('pharmacist_id')->nullable()->constrained('pharmacists')->onDelete('cascade');
            $table->string('image')->nullable();
            $table->integer('like')->default('0');
            $table->integer('dislike')->default('0');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
