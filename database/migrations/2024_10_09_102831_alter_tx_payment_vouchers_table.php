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
            $table->string('payment_voucher_no',15)->nullable()->comment('no pembayaran supplier')->change();
            $table->dateTime('pv_created_at')->nullable()->after('diskon_pembelian')->comment('tgl/jam terbentuknya no PV');
            $table->dateTime('ps_created_at')->nullable()->after('pv_created_at')->comment('tgl/jam terbentuknya no PV plan');
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
