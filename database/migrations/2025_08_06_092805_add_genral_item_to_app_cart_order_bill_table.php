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
        Schema::table('app_cart_order_bill', function (Blueprint $table) {
            $table->json('genral_item')->nullable()->after('cart_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
         Schema::table('app_cart_order_bill', function (Blueprint $table) {
            $table->dropColumn('genral_item');
        });
    }
};
