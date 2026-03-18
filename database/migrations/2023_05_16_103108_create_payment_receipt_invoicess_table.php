<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tx_payment_receipt_invoices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('payment_receipt_id')->comment('FK ke table tx_payment_receipts');
            $table->unsignedBigInteger('delivery_order_id')->comment('FK ke table tx_delivery_orders');
            $table->string('invoice_no',255)->nullable();
            $table->string('description',1000)->nullable();
            $table->decimal('total_payment',20,2);
            $table->char('is_full_payment',1)->default('Y')->nullable()->comment('jika ada pembayaran belum 100%');
            $table->char('active', 1)->default('Y');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by');
            $table->timestamps();

            $table->foreign('payment_receipt_id')->references('id')->on('tx_payment_receipts');
            $table->foreign('delivery_order_id')->references('id')->on('tx_delivery_orders');
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('updated_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payment_receipt_invoicess');
    }
};
