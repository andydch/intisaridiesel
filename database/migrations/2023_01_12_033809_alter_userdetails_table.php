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
        Schema::table('userdetails', function (Blueprint $table) {
            $table->unsignedBigInteger('country_id')->after('address');
            $table->unsignedBigInteger('province_id')->after('country_id')->nullable();
            $table->unsignedBigInteger('city_id')->after('province_id');
            $table->unsignedBigInteger('district_id')->after('city_id')->nullable();
            $table->unsignedBigInteger('sub_district_id')->after('district_id')->nullable();
            $table->string('postcode', 6)->after('sub_district_id')->nullable();

            $table->foreign('country_id')->references('id')->on('mst_countries');
            $table->foreign('province_id')->references('id')->on('mst_provinces');
            $table->foreign('city_id')->references('id')->on('mst_cities');
            $table->foreign('district_id')->references('id')->on('mst_districts');
            $table->foreign('sub_district_id')->references('id')->on('mst_sub_districts');
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
