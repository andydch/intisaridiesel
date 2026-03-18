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
        Schema::table('tx_nota_retur_non_taxes', function (Blueprint $table) {
            $table->decimal('total_price',20,2)->default(0)->after('total_qty');
            $table->dropColumn('total_before_vat');
            $table->dropColumn('total_after_vat');
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
