<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('auto_variants_in_weight_btn')->nullable();
            $table->json('auto_variants_in_weight')->nullable();
            $table->string('auto_variants_in_amount_btn')->nullable();
            $table->json('auto_variants_in_amount')->nullable();
            $table->string('custom_price_btn')->nullable();
            $table->json('custom_price')->nullable();
            $table->string('packaging_btn')->nullable();
            $table->string('packaging')->nullable();
            $table->string('custom_variant_btn')->nullable();
            $table->string('is_variant')->nullable();
            $table->string('product_type')->nullable();
            $table->string('weight_to')->nullable();
            $table->string('weight_from')->nullable();
        });
    }

    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'auto_variants_in_weight_btn',
                'auto_variants_in_weight',
                'auto_variants_in_amount_btn',
                'auto_variants_in_amount',
                'custom_price_btn',
                'custom_price',
                'packaging_btn',
                'packaging',
                'custom_variant_btn',
                'is_variant',
                'product_type',
                'weight_to',
                'weight_from',
            ]);
        });
    }
};
