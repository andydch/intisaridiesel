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
        Schema::table('mst_suppliers', function (Blueprint $table) {
            $table->unsignedBigInteger('currency1')->after('phone2');
            $table->unsignedBigInteger('currency2')->after('currency1');

            $table->foreign('currency1')->references('id')->on('mst_globals');
            $table->foreign('currency2')->references('id')->on('mst_globals');
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
