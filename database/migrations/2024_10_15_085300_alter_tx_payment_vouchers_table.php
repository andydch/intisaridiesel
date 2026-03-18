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
            $table->decimal('vat_num',5,2)->default(0)->nullable()->after('ps_created_at')->comment('berisi prosentase ppn jika menggunakan PPN');
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
