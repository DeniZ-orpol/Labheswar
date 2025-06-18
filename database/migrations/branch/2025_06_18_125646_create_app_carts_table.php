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
        Schema::create('app_carts', function (Blueprint $table) {
            $table->id();
            $table->string('cart_id')->nullable();
            $table->unsignedBigInteger('branch_user_id')->nullable();
            $table->string('sub_total')->nullable();
            $table->string('texes')->nullable();
            $table->string('total_amount')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('app_carts');
    }
};
