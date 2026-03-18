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
            $table->string('cust_unit_no',255)->nullable()->comment('customer unit number')->after('pic_name');
            $table->unsignedBigInteger('cust_shipping_address_id')->nullable()->after('total_after_vat')->comment('FK ke mst_customer_shipment_address');

            $table->foreign('cust_shipping_address_id')->references('id')->on('mst_customer_shipment_address');
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
