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
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            // $table->unsignedBigInteger('user_id');
            $table->string('name');
            $table->string('code', 20)->unique();
            $table->string('location');
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
            $table->string('gst_no')->nullable();
            $table->string('database_name', 100);
            $table->string('connection_name', 50);
            $table->string('db_username')->nullable();
            $table->string('db_password')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->unsignedBigInteger('branch_admin')->nullable();
            // $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            // $table->foreign('branch_admin')->references('id')->on('branch_users')->onDelete('cascade');
            // $table->foreign('branch_admin')->references('id')->on('branch_users')->onDelete('cascade');
            $table->timestamps();

            $table->index(['status']);
            $table->index(['code']);
        });
    }
    // public function up(): void
    // {
    //     Schema::create('branches', function (Blueprint $table) {
    //         $table->id();
    //         $table->unsignedBigInteger('user_id');
    //         $table->string('name');
    //         $table->string('location');
    //         $table->string('latitude')->nullable();
    //         $table->string('longitude')->nullable();
    //         $table->string('gst_no')->nullable();
    //         $table->unsignedBigInteger('branch_admin')->nullable();
    //         $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
    //         $table->foreign('branch_admin')->references('id')->on('users')->onDelete('cascade');
    //         $table->timestamps();
    //     });
    // }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('branches');
        // Schema::dropIfExists('branches');
    }
};
