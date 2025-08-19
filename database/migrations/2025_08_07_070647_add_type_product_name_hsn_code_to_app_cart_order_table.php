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
        Schema::table('app_cart_order', function (Blueprint $table) {
            $table->string('type')->nullable()->after('product_quantity');
            $table->string('product_name')->nullable()->after('type');
            $table->string('hsn_code')->nullable()->after('product_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('app_cart_order', function (Blueprint $table) {
            $table->dropColumn(['type', 'product_name', 'hsn_code']);
        });
    }
};
