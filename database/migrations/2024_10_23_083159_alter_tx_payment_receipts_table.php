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
        Schema::table('tx_payment_receipts', function (Blueprint $table) {
            $table->decimal('diskon_pembelian',20,2)->nullable()->after('payment_total');
            $table->decimal('admin_bank',20,2)->nullable()->after('diskon_pembelian');
            $table->decimal('biaya_kirim',20,2)->nullable()->after('admin_bank');
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
