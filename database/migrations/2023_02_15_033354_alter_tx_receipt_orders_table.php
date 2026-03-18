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
            $table->unsignedBigInteger('approved_by')->nullable()->after('company_id')->comment('FK ke master users');
            $table->dateTime('approved_at')->nullable()->after('approved_by');

            // $table->foreign('approved_by')->references('id')->on('users');
            $table->index('approved_by');
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
