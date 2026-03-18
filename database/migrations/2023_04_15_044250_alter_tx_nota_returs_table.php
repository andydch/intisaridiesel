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
            $table->dropColumn('delivery_order');
            $table->unsignedBigInteger('delivery_order_id')->after('invoice_no')->nullable();

            $table->foreign('delivery_order_id')->references('id')->on('tx_delivery_orders');
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
