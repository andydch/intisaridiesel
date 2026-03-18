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
        Schema::table('mst_sub_districts', function (Blueprint $table) {
            $table->renameColumn('sub_district_id', 'district_id');

            // $table->unsignedBigInteger('district_id')->comment('FK ke mst_districts')->change();
            // $table->foreign('district_id')->references('id')->on('mst_districts');
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
