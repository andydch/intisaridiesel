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
        Schema::table('tx_receipt_orders', function (Blueprint $table) {
            $table->decimal('total_before_vat_rp',20,2)->after('total_before_vat')->nullable()->comment('nilai rupiah utk total sebelum ppn');
            $table->decimal('total_vat_rp',20,2)->after('total_vat')->nullable()->comment('nilai rupiah utk ppn');
            $table->decimal('total_after_vat_rp',20,2)->after('total_after_vat')->nullable()->comment('nilai rupiah utk total sesudah ppn');
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
