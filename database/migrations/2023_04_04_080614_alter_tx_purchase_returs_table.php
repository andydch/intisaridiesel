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
        Schema::table('tx_purchase_returs', function (Blueprint $table) {
            $table->dropColumn('invoice_no');
            $table->unsignedBigInteger('receipt_order_id')->after('supplier_name');

            $table->foreign('receipt_order_id')->references('id')->on('tx_receipt_orders');
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
