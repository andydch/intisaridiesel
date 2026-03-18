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
        Schema::table('tx_payment_vouchers', function (Blueprint $table) {
            $table->unsignedBigInteger('tagihan_supplier_id')->nullable()->after('payment_reference_id')->comment('FK ke tx_tagihan_suppliers');

            $table->foreign('tagihan_supplier_id')->references('id')->on('tx_tagihan_suppliers');
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
