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
            $table->date('next_plan_date')->nullable()->after('is_draft')->comment('Tanggal rencana penerimaan berikutnya, jika pembayaran parsial');
            $table->char('next_plan_date_status', 1)->default('N')->nullable()->after('next_plan_date')->comment('Y=Next plan date boleh edit, N=Next plan date tidak boleh edit');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tx_payment_vouchers', function (Blueprint $table) {
            $table->dropColumn('next_plan_date');
            $table->dropColumn('next_plan_date_status');
        });
    }
};
