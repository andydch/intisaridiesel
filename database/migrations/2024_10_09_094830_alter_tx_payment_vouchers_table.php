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
            $table->decimal('admin_bank',20,2)->default(0)->comment('biaya lain')->after('remark');
            $table->decimal('biaya_asuransi',20,2)->default(0)->comment('biaya lain')->after('admin_bank');
            $table->decimal('biaya_kirim',20,2)->default(0)->comment('biaya lain')->after('biaya_asuransi');
            $table->decimal('diskon_pembelian',20,2)->default(0)->comment('biaya lain')->after('biaya_kirim');
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
