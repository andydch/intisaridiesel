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
        Schema::table('tx_purchase_inquiries', function (Blueprint $table) {
            $table->dropForeign('tx_purchase_inquiries_cancel_by_foreign');
            $table->dropColumn('cancel_by');

            $table->unsignedBigInteger('canceled_by')->after('updated_by');
            $table->foreign('canceled_by')->references('id')->on('users');
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
