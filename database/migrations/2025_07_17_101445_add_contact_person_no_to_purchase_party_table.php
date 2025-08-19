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
        Schema::table('purchase_party', function (Blueprint $table) {
            //
            $table->string('contact_person_no')->nullable()->after('contact_person');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_party', function (Blueprint $table) {
            //
            $table->dropColumn('contact_person_no');
        });
    }
};
