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
        Schema::create('many_to_one', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ledger_id')->nullable();
            $table->date('date')->nullable();
            $table->string('entry_no')->nullable();
            $table->unsignedBigInteger('conversion_item');
            $table->string('qty')->nullable();
            $table->json('raw_item');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('many_to_one');
    }
};
