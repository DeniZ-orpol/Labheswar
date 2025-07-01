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
        Schema::create('ledgers', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('station')->nullable();
            $table->string('acc_group')->nullable();
            $table->string('balancing_method')->nullable();
            $table->string('mail_to')->nullable();
            $table->string('address')->nullable();
            $table->string('pin_code')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->string('contact_person')->nullable();
            $table->string('designation')->nullable();
            $table->string('phone_no')->nullable();
            $table->string('gst_no')->nullable();
            $table->string('state')->nullable();
            $table->string('gst_heading')->nullable();
            $table->string('note')->nullable();
            $table->string('ledger_category')->nullable();
            $table->string('country')->nullable();
            $table->string('pan_no')->nullable();
            $table->string('type')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ledgers');
    }
};
