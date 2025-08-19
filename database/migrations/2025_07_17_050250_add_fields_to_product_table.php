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
        Schema::table('products', function (Blueprint $table) {
            $table->string('use_static_variant')->nullable(); // value: yes, no
            $table->string('packaging_type')->nullable();
            $table->json('custom_variant')->nullable();
            $table->string('use_custom_variant')->nullable(); // value: yes, no
            $table->string('loose_below_weight')->nullable();
            $table->string('loose_below_price')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product', function (Blueprint $table) {
            $table->dropColumn(['use_static_variant','packaging_type','custom_variant','use_custom_variant','loose_below_weight','loose_below_price']);
        });
    }
};
