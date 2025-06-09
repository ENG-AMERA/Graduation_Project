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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description');
            $table->double('price')->nullable();
            $table->integer('quantity')->nullable();
            $table->string('image')->nullable();
            $table->double('evaluation')->nullable();
            $table->boolean('has_variants')->default(false);
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
            $table->foreignId('pharma_id')->constrained('pharmas')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
