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
        Schema::create('tx_invoice_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoice_id')->comment('FK ke tx_invoices');
            $table->unsignedBigInteger('fk_id')->comment('FK ke tx_delivery_orders');
            $table->string('delivery_order_no',15);
            $table->date('delivery_order_date');
            $table->unsignedBigInteger('tax_invoice_id')->comment('FK ke tx_tax_invoices');
            $table->string('fp_no',255);
            $table->decimal('total',20,2)->default(0)->nullable();
            $table->decimal('vat',20,2)->default(0)->nullable();
            $table->decimal('grand_total',20,2)->default(0)->nullable();
            $table->char('active', 1)->default('Y');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by');
            $table->timestamps();

            $table->foreign('invoice_id')->references('id')->on('tx_invoices');
            $table->foreign('fk_id')->references('id')->on('tx_delivery_orders');
            $table->foreign('tax_invoice_id')->references('id')->on('tx_tax_invoices');
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
        Schema::dropIfExists('tx_invoice_details');
    }
};
