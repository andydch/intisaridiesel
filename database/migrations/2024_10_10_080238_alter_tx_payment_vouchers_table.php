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
            $table->dateTime('created_at_pv')->nullable()->after('active')->comment('tgl/jam PV terbentuk');
            $table->dateTime('created_at_ps')->nullable()->after('created_at_pv')->comment('tgl/jam PS terbentuk');
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
