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
            $table->unsignedBigInteger('sales_quotation_id')->nullable()->comment('FK ke tx_sales_quotation')->after('sales_order_no');

            $table->foreign('sales_quotation_id')->references('id')->on('tx_sales_quotations');
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
