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
        Schema::table('tx_stock_assemblys', function (Blueprint $table) {
            $table->unsignedBigInteger('branch_id')->nullable()->after('qty')->comment('FK ke mst_branches');

            $table->foreign('branch_id')->references('id')->on('mst_branches');
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
