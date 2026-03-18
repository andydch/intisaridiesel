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
            $table->char('approved_status', 1)->nullable()->after('company_id')->comment('A:approved;R:rejected');
            $table->string('rejected_reason', 2048)->after('approved_status')->nullable()->comment('alasan jika rejected');
            $table->unsignedBigInteger('approved_by')->after('rejected_reason')->nullable();
            $table->dateTime('approved_at')->nullable()->after('approved_by');

            $table->foreign('approved_by')->references('id')->on('users');
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
