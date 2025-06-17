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
        Schema::create('purchase', function (Blueprint $table) {
            $table->id();
            // $table->unsignedBigInteger('purchase_receipt_id')->after('id');
            $table->date('bill_date')->nullable();
            $table->unsignedBigInteger('purchase_party_id')->nullable();
            $table->string('bill_no')->nullable(); // Purchase Bill number
            $table->date('delivery_date')->nullable();
            $table->string('gst')->nullable();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->string('product')->nullable();
            $table->decimal('mrp')->nullable();
            $table->decimal('box')->nullable();
            $table->decimal('pcs')->nullable();
            $table->decimal('free')->nullable(); // Free pcs number with box
            $table->decimal('p_rate')->nullable(); // Purchase Rate without GST / Base Rate
            $table->decimal('discount')->nullable(); // Discount in % before GST
            $table->decimal('lumpsum')->nullable(); // Discount in Rs. before GST
            $table->decimal('amount')->nullable(); // TOTAL AMOUNT = BASE RATE(p_rate) * PCS - DISCOUNT * GST 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase');
    }
};
