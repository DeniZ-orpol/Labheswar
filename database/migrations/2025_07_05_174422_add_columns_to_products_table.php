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
            $table->string('price_1')->nullable();
            $table->string('price_2')->nullable();
            $table->string('price_3')->nullable();
            $table->string('price_4')->nullable();
            $table->string('price_5')->nullable();
            $table->string('Kg_1')->nullable();
            $table->string('Kg_2')->nullable();
            $table->string('Kg_3')->nullable();
            $table->string('Kg_4')->nullable();
            $table->string('Kg_5')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['price_1', 'price_2', 'price_3', 'price_4', 'price_5', 'Kg_1', 'Kg_2', 'Kg_3', 'Kg_4', 'Kg_5']);
        });
    }
};
