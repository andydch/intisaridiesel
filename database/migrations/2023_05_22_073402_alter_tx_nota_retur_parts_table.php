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
        Schema::table('tx_nota_retur_parts', function (Blueprint $table) {
            $table->dropForeign('tx_nota_retur_parts_delivery_order_id_foreign');
            $table->dropForeign('tx_nota_retur_parts_delivery_order_part_id_foreign');
            $table->dropColumn('delivery_order_id');
            $table->dropColumn('delivery_order_part_id');

            $table->unsignedBigInteger('invoice_part_id')->after('nota_retur_id');
            $table->foreign('invoice_part_id')->references('id')->on('tx_invoice_parts');
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
