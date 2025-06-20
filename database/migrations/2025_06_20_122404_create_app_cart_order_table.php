<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('app_cart_order', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('cart_id')->nullable();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->string('firm_id')->nullable();
            $table->string('product_weight')->nullable();
            $table->string('product_price')->nullable();
            $table->string('product_quantity')->nullable();
            $table->string('taxes')->nullable();
            $table->string('sub_total')->nullable();
            $table->string('total_amount')->nullable();
            $table->string('gst')->nullable();
            $table->integer('gst_p')->nullable();
            $table->string('return_product')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('app_carts_order');
    }
};
