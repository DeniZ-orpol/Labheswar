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
        Schema::create('app_cart_order_bill', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cart_id')->nullable();
            $table->string('total_texes')->nullable();
            $table->string('sub_total')->nullable();
            $table->string('total')->nullable();
            $table->string('customer_name')->nullable();
            $table->string('customer_contact')->nullable();
            $table->string('razorpay_payment_id')->nullable();
            $table->string('bill_due_date')->nullable();
            $table->string('payment_status')->nullable();
            $table->string('status')->nullable();
            $table->string('user_id')->nullable();
            $table->string('discount_rs')->nullable();
            $table->string('discount_percentage')->nullable();
            $table->integer('return_order')->nullable();
            $table->string('is_delivery')->nullable();
            $table->string('address_id')->nullable();
            $table->string('ship_to_name')->nullable();
            $table->string('expected_delivery_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('app_cart_order_bill');
    }
};
