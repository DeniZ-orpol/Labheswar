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
        Schema::create('one_to_many', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ledger_id')->nullable();
            $table->date('date')->nullable();
            $table->string('entry_no')->nullable();
            $table->string('qty')->nullable();
            $table->unsignedBigInteger('raw_item');
            $table->json('item_to_create');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('one_to_many');
    }
};
