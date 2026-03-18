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
        Schema::table('mst_customers', function (Blueprint $table) {
            $table->string('phone1',32)->nullable()->change();
            $table->string('pic1_phone',32)->nullable()->change();
            $table->string('pic1_email',64)->nullable()->change();
            $table->string('npwp_no',24)->nullable()->change();
            $table->string('npwp_address',1024)->nullable()->change();
            $table->unsignedBigInteger('npwp_province_id')->nullable()->change();
            $table->unsignedBigInteger('npwp_city_id')->nullable()->change();
            $table->unsignedBigInteger('npwp_district_id')->nullable()->change();
            $table->unsignedBigInteger('npwp_sub_district_id')->nullable()->change();
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
