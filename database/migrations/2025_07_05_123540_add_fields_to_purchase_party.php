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
            $table->string('ledger_group')->nullable()->after('pincode');
            $table->string('balancing_method')->nullable()->after('ledger_group');
            $table->string('mail_to')->nullable()->after('balancing_method');
            $table->string('contact_person')->nullable()->after('mail_to');
            $table->string('designation')->nullable()->after('contact_person');
            $table->string('state')->nullable()->after('station');
            $table->string('gst_heading')->nullable()->after('gst_number');
            $table->string('note')->nullable()->after('designation');
            $table->string('ledger_category')->nullable()->after('note');
            $table->string('country')->nullable()->after('ledger_category');
            $table->string('pan_no')->nullable()->after('country');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_party', function (Blueprint $table) {
            $table->dropColumn('ledger_group');
            $table->dropColumn('balancing_method');
            $table->dropColumn('mail_to');
            $table->dropColumn('contact_person');
            $table->dropColumn('designation');
            $table->dropColumn('state');
            $table->dropColumn('gst_heading');
            $table->dropColumn('note');
            $table->dropColumn('ledger_category');
            $table->dropColumn('country');
            $table->dropColumn('pan_no');
        });
    }
};
