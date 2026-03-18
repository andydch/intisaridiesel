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
        Schema::create('mst_branches', function (Blueprint $table) {
            $table->id();
            $table->string('initial', 12)->comment('inisial cabang');
            $table->string('name', 255);
            $table->string('slug', 255);
            $table->unsignedBigInteger('province_id')->comment('FK ke master propinsi');
            $table->unsignedBigInteger('city_id')->comment('FK ke master city');
            $table->unsignedBigInteger('district_id')->comment('FK ke master kecamatan');
            $table->unsignedBigInteger('sub_district_id')->comment('FK ke master kelurahan');
            $table->string('address', 1024);
            $table->string('post_code', 6);
            $table->string('phone1', 32);
            $table->string('phone2', 32)->nullable();
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by');
            $table->timestamps();

            $table->foreign('province_id')->references('id')->on('mst_provinces');
            $table->foreign('city_id')->references('id')->on('mst_cities');
            $table->foreign('district_id')->references('id')->on('mst_districts');
            $table->foreign('sub_district_id')->references('id')->on('mst_sub_districts');
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
        Schema::dropIfExists('mst_branches');
    }
};
