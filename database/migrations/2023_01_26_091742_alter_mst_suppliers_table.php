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
        Schema::table('mst_suppliers', function (Blueprint $table) {
            $table->integer('top')->default(0)->nullable()->after('npwp_sub_district_id');
            $table->decimal('credit_limit',20,2)->default(0)->nullable('top');
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
