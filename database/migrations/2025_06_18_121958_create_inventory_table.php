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
        Schema::create('inventory', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained(); // Assumes products table exists
            $table->enum('type', ['in', 'out']);
            $table->decimal('quantity',10,2);
            $table->string('unit')->nullable(); // NOS, BOX, PIECE, etc.
            $table->string('reason')->nullable(); // Opening Stock, Purchase, Sale, etc.
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventories');
    }
};
