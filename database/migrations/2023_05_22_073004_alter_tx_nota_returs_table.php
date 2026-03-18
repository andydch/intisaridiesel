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
            $table->dropColumn('invoice_no');

            $table->unsignedBigInteger('invoice_id')->after('nota_retur_date');
            $table->foreign('invoice_id')->references('id')->on('tx_invoices');
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
