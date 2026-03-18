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
        Schema::table('tx_purchase_memo_parts', function (Blueprint $table) {
            $table->unsignedBigInteger('memo_id')->after('id')->comment('FK ke table tx_purchase_memos');

            $table->foreign('memo_id')->references('id')->on('tx_purchase_memos');
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
