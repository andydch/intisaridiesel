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
            $table->dropForeign('tx_payment_vouchers_coa_id_foreign');

            $table->date('payment_date')->nullable()->comment('berisi journal date')->change();
            $table->string('payment_voucher_plan_no',15)->nullable()->after('payment_voucher_no')->comment('no plan');
            $table->unsignedBigInteger('coa_id')->nullable()->comment('berisi pilihan coa no rekening bank')->change();
            $table->integer('payment_mode')->after('payment_total')->nullable()->comment('pilihan mode pembayaran cash/bank');
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
