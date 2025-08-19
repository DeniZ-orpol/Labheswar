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
        Schema::create('stockissues', function (Blueprint $table) {
            $table->id();
            $table->string('ledger');
            $table->string('issue_no');
            $table->date('date');
            $table->unsignedBigInteger('from_branch')->nullable();
            $table->unsignedBigInteger('to_branch')->nullable();
            $table->json('products');
            $table->unsignedBigInteger('total_amount');
            $table->string('status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stockissues');
    }
};
