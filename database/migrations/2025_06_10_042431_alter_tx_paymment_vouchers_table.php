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
            $table->decimal('payment_total_after_vat', 20, 2)->nullable()->default(0)->after('payment_total');
        });

        Schema::table('tx_payment_voucher_invoices', function (Blueprint $table) {
            $table->decimal('total_payment_after_vat', 20, 2)->nullable()->default(0)->after('total_payment');
            $table->decimal('total_payment_before_retur_after_vat', 20, 2)->nullable()->default(0)->after('total_payment_before_retur');
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
