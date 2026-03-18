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
        Schema::table('tx_purchase_orders', function (Blueprint $table) {
            $table->unsignedBigInteger('quotation_id')->nullable()->comment('FK ke table tx_purchase_quotation')->after('purchase_no');
            $table->date('est_supply_date')->nullable()->after('company_sub_district_id');
            $table->unsignedBigInteger('courier_id')->nullable()->comment('FK ke master courier')->after('est_supply_date');

            $table->foreign('quotation_id')->references('id')->on('tx_purchase_quotations');
            $table->foreign('courier_id')->references('id')->on('mst_couriers');
        });

        Schema::table('tx_purchase_order_parts', function (Blueprint $table) {
            $table->string('description',1024)->nullable()->after('price');
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
