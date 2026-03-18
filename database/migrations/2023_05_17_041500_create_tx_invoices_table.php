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
        Schema::create('tx_invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_no',15);
            $table->string('tax_invoice_no',64)->comment('no faktur, berisi kumpulan angka');
            $table->unsignedBigInteger('customer_id')->comment('FK ke master customer');
            $table->unsignedBigInteger('delivery_order_id')->comment('FK ke tx_delivery_orders');
            $table->date('invoice_date')->comment('tanggal invoice');
            $table->date('invoice_expired_date')->comment('tanggal invoice expired');
            $table->decimal('do_total',20,2)->comment('total pembayaran dari DO yang dipilih tanpa vat');
            $table->decimal('do_vat',20,2)->comment('nilai vat dari DO yang dipilih');
            $table->decimal('do_grandtotal_vat',20,2)->comment('grand total pembayaran dari DO yang dipilih');
            $table->text('remark')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->dateTime('approved_at')->nullable();
            $table->unsignedBigInteger('canceled_by');
            $table->dateTime('canceled_at')->nullable();
            $table->dateTime('draft_at')->nullable();
            $table->dateTime('draft_to_created_at')->nullable();
            $table->char('is_draft',1)->default('N')->nullable();
            $table->char('active', 1)->default('Y');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by');
            $table->timestamps();

            $table->foreign('customer_id')->references('id')->on('mst_customers');
            $table->foreign('delivery_order_id')->references('id')->on('tx_delivery_orders');
            $table->foreign('approved_by')->references('id')->on('users');
            $table->foreign('canceled_by')->references('id')->on('users');
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
        Schema::dropIfExists('tx_invoices');
    }
};
