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
        Schema::table('tx_delivery_order_non_tax_parts', function (Blueprint $table) {
            $table->dropForeign('tx_delivery_order_non_tax_parts_sales_order_id_foreign');
            $table->dropForeign('tx_delivery_order_non_tax_parts_sales_order_part_id_foreign');

            $table->foreign('sales_order_id')->references('id')->on('tx_surat_jalans');
            $table->foreign('sales_order_part_id')->references('id')->on('tx_surat_jalan_parts');
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
