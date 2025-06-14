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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('product_name');
            $table->string('barcode')->index();
            $table->string('search_option')->nullable();
            $table->string('unit_types')->nullable();
            $table->boolean('decimal_btn')->description('Sell in loose quantity')->nullable();
            $table->foreignId('company')->nullable()->constrained('companies')->onDelete('set null');
            $table->foreignId('category_id')->nullable()->constrained('categories')->onDelete('set null');
            $table->foreignId('hsn_code_id')->nullable()->constrained('hsn_codes')->onDelete('set null');
            $table->decimal('sgst')->nullable();
            $table->decimal('cgst1')->nullable();
            $table->decimal('cgst2')->nullable();
            $table->decimal('cess')->nullable();
            $table->decimal('mrp')->nullable();
            $table->decimal('purchase_rate')->nullable();
            $table->decimal('sale_rate_a')->nullable();
            $table->decimal('sale_rate_b')->nullable();
            $table->decimal('sale_rate_c')->nullable();
            $table->boolean('sale_online')->nullable();
            $table->boolean('gst_active')->nullable();
            $table->string('converse_carton')->nullable();
            $table->string('converse_box')->nullable();
            $table->string('converse_pcs')->nullable();
            $table->string('negative_billing')->nullable()->default('no');
            $table->integer('min_qty')->nullable();
            $table->integer('reorder_qty')->nullable();
            $table->string('discount')->nullable();
            $table->integer('max_discount')->nullable();
            $table->string('discount_scheme')->nullable();
            $table->boolean('bonus_use')->nullable();
            $table->timestamps();

            $table->index('category_id');
            $table->index('hsn_code_id');
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
