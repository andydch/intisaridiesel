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
        Schema::table('tx_nota_returs', function (Blueprint $table) {
            $table->dropForeign('tx_nota_returs_invoice_id_foreign');
            $table->dropColumn('invoice_id');
        });

        Schema::table('tx_nota_retur_parts', function (Blueprint $table) {
            $table->dropForeign('tx_nota_retur_parts_invoice_part_id_foreign');
            $table->dropColumn('invoice_part_id');

            $table->unsignedBigInteger('sales_order_part_id')->nullable()->comment('FK ke tx_sales_order_parts');

            $table->foreign('sales_order_part_id')->references('id')->on('tx_receipt_order_parts');
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
