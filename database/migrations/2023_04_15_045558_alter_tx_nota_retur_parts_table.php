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
            $table->unsignedBigInteger('delivery_order_part_id')->after('delivery_order_id')->nullable();

            $table->foreign('delivery_order_part_id')->references('id')->on('tx_delivery_order_parts');
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
