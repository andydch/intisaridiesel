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
            $table->dropForeign('tx_receipt_orders_weight_type_id_foreign');
            $table->dropColumn('weight_type_id');

            $table->unsignedBigInteger('weight_type_id01')->nullable()->after('vessel_no');
            $table->unsignedBigInteger('weight_type_id02')->nullable()->after('weight_type_id01');

            $table->foreign('weight_type_id01')->references('id')->on('mst_globals');
            $table->foreign('weight_type_id02')->references('id')->on('mst_globals');
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
