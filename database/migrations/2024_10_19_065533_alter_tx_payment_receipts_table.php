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
            $table->string('payment_receipt_no',15)->nullable()->change();
            $table->string('payment_receipt_plan_no',15)->nullable()->after('payment_receipt_no')->comment('no plan');
            $table->integer('payment_mode')->nullable()->after('payment_total')->comment('pilihan mode pembayaran cash/bank');
            $table->dateTime('pr_created_at')->nullable()->after('remark');
            $table->dateTime('ps_created_at')->nullable()->after('pr_created_at');

            $table->dropForeign('tx_payment_receipts_coa_id_foreign');
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
