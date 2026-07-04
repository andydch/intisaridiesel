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
        Schema::table('tx_payment_plan_per_rc_orders', function (Blueprint $table) {
            $table->char('is_pv_approved', 1)->default('N')->nullable()->after('actual_payment')->comment('Y=PV sudah di approve, N=PV belum di approve');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tx_payment_plan_per_rc_orders', function (Blueprint $table) {
            $table->dropColumn('is_pv_approved');
        });
    }
};
