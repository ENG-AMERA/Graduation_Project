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
        Schema::create('pharmacists', function (Blueprint $table) {
            $table->id();
            $table->string('certificate');
            $table->string('description');
            $table->string('accept')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignId('pharma_id')->constrained('pharmas')->onDelete('cascade');
            $table->integer('point_value')->default(100); // 1 point = 100 SY
            $table->boolean('accept_point')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pharmacists');

    Schema::table('pharmacists', function (Blueprint $table) {
        $table->dropColumn('accept_point');
    });
    }
};
