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
        Schema::table('tx_delivery_orders', function (Blueprint $table) {
            $table->unsignedBigInteger('tax_invoice_id')->nullable()->after('sales_order_no_all')->comment('FK ke tx_tax_invoices');

            $table->foreign('tax_invoice_id')->references('id')->on('tx_tax_invoices');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
};
