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
        Schema::table('tx_delivery_orders', function (Blueprint $table) {
            $table->dateTime('faktur_dl_date')->nullable()->after('number_of_prints')->comment('tgl/jam faktur di download utk coretax - GMT0');
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
