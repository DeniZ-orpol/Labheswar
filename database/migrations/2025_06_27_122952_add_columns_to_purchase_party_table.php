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
            $table->string('company_name')->after('party_name')->nullable();
            $table->string('gst_number')->after('company_name')->nullable();
            $table->string('mobile_no')->after('gst_number')->nullable();
            $table->string('email')->after('mobile_no')->nullable();
            $table->longText('address')->after('email')->nullable();
            $table->string('station')->after('address')->nullable();
            $table->string('acc_no')->after('station')->nullable();
            $table->string('ifsc_code')->after('acc_no')->nullable();
            $table->string('pincode')->after('ifsc_code')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_party', function (Blueprint $table) {
            $table->dropColumn('company_name');
            $table->dropColumn('gst_number');
            $table->dropColumn('mobile_no');
            $table->dropColumn('email');
            $table->dropColumn('address');
            $table->dropColumn('station');
            $table->dropColumn('acc_no');
            $table->dropColumn('ifsc_code');
            $table->dropColumn('pincode');
        });
    }
};
