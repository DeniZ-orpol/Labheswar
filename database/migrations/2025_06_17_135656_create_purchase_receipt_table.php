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
        Schema::create('purchase_receipt', function (Blueprint $table) {
            $table->id();

            // Main purchase details
            $table->date('bill_date');
            $table->unsignedBigInteger('purchase_party_id');
            $table->string('bill_no', 255);
            $table->date('delivery_date')->nullable();
            $table->string('gst_status', 10)->default('on'); // 'on' or 'off'

            // Totals
            $table->decimal('subtotal', 12, 2)->default(0.00); // Total before GST
            $table->decimal('total_discount', 12, 2)->default(0.00); // Total discount amount
            $table->decimal('total_gst_amount', 12, 2)->default(0.00); // Total GST amount
            $table->decimal('total_amount', 12, 2)->default(0.00); // Final total amount

            // Additional fields
            // $table->text('remarks')->nullable();
            $table->string('receipt_status', 20)->default('completed'); // completed, pending, cancelled

            // Audit fields
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            // Indexes for better performance
            $table->index('bill_date');
            $table->index('purchase_party_id');
            $table->index('bill_no');
            $table->index('receipt_status');
            $table->index('created_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_receipt');
    }
};
