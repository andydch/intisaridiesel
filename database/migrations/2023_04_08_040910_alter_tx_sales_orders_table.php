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
        Schema::table('tx_sales_orders', function (Blueprint $table) {
            $table->dropForeign('tx_sales_orders_cust_shipping_address_id_foreign');
            $table->dropColumn('cust_shipping_address_id');

            $table->unsignedBigInteger('cust_shipment_address')->comment('FK ke master cust shipmen address')->change();
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
