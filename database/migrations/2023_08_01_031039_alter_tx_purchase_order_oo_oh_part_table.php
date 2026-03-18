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
        Schema::table('tx_purchase_order_oo_oh_parts', function (Blueprint $table) {
            $table->unsignedBigInteger('purchase_order_part_id')->after('purchase_order_id');

            $table->foreign('purchase_order_part_id')->references('id')->on('tx_purchase_order_parts');
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
