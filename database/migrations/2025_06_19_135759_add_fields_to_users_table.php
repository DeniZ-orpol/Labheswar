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
        Schema::table('users', function (Blueprint $table) {
            $table->json('permission')->nullable();
            $table->string('is_active')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->timestamp('password_changed_at')->nullable();
            $table->unsignedBigInteger('branch_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('permission');
            $table->dropColumn('is_active');
            $table->dropColumn('last_login_at');
            $table->dropColumn('password_changed_at');
            $table->dropColumn('branch_id');
        });
    }
};
