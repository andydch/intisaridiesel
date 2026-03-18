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
        Schema::table('tx_payment_receipt_invoices', function (Blueprint $table) {
            $table->unsignedBigInteger('invoice_id')->nullable()->after('payment_receipt_id');

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
