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
        Schema::create('mst_salesmans', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255)->comment('nama salesman');
            $table->string('slug', 260);
            $table->unsignedBigInteger('branch_id')->comment('FK ke table master branch');
            $table->unsignedBigInteger('province_id')->comment('FK ke master propinsi');
            $table->unsignedBigInteger('city_id')->comment('FK ke master city');
            $table->unsignedBigInteger('district_id')->comment('FK ke master kecamatan');
            $table->unsignedBigInteger('sub_district_id')->comment('FK ke master kelurahan');
            $table->string('address', 1024)->comment('residence address');
            $table->string('post_code', 6);
            $table->string('id_no', 32)->comment('NIK atau no ktp');
            $table->string('email', 64);
            $table->unsignedBigInteger('gender_id')->comment('FK ke table master global');
            $table->string('mobilephone', 32);
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by');
            $table->timestamps();

            $table->foreign('branch_id')->references('id')->on('mst_branches');
            $table->foreign('province_id')->references('id')->on('mst_provinces');
            $table->foreign('city_id')->references('id')->on('mst_cities');
            $table->foreign('district_id')->references('id')->on('mst_districts');
            $table->foreign('sub_district_id')->references('id')->on('mst_sub_districts');
            $table->foreign('gender_id')->references('id')->on('mst_globals');
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('updated_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mst_salesmans');
    }
};
