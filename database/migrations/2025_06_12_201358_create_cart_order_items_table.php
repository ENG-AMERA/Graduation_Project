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
        Schema::create('cart_order_items', function (Blueprint $table) {
            $table->id();
             $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('cart_order_id')->constrained('cart_orders')->onDelete('cascade');
            $table->foreignId('type_id')->nullable()->constrained('types')->onDelete('cascade');
            $table->double('totalprice');
            $table->integer('quantity');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cart_order_items');
    }
};
