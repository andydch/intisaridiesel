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
        Schema::table('tx_general_journal_details', function (Blueprint $table) {
            $table->unsignedBigInteger('coa_detail_id')->nullable()->after('coa_id')->comment('FK ke mst coa detail');

            $table->foreign('coa_detail_id')->references('id')->on('mst_coa_details');
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
