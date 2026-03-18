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
            $table->decimal('penerimaan_lainnya', 20, 2)->nullable()->after('biaya_kirim');
        });

        Schema::table('log_tx_payment_receipts', function (Blueprint $table) {
            $table->decimal('penerimaan_lainnya', 20, 2)->nullable()->after('biaya_kirim');
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
