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
        Schema::create('stocks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('chalan_id')->nullable();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->date('date')->nullable();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->decimal('mrp')->nullable();
            $table->decimal('box')->nullable();
            $table->decimal('pcs')->nullable();
            $table->decimal('amount')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stocks');
    }
};
