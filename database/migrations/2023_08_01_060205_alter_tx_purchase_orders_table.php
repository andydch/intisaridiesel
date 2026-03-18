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
            $table->unsignedBigInteger('courier_id')->after('branch_address')->nullable()->comment('FK ke master courier');

            $table->foreign('courier_id')->references('id')->on('mst_couriers');
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
