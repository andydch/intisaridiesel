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
        Schema::table('tx_purchase_orders', function (Blueprint $table) {
            // $table->dropIndex('tx_purchase_orders_cancel_by_foreign');
            $table->dropForeign('tx_purchase_orders_cancel_by_foreign');
            $table->dropColumn(['cancel_by', 'cancel_at']);
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
