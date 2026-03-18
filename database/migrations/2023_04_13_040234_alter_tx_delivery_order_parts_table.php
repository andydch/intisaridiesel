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
        Schema::table('tx_delivery_order_parts', function (Blueprint $table) {
            $table->unsignedBigInteger('sales_order_part_id')->nullable()->after('sales_order_id');

            $table->foreign('sales_order_part_id')->references('id')->on('tx_sales_order_parts');
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
