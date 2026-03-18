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
        Schema::create('mst_couriers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('entity_type_id')->comment('FK ke table master global - entity type');
            $table->string('name', 255)->comment('nama kurir');
            $table->string('slug', 260);
            $table->string('office_address', 1024)->comment('office address');
            $table->unsignedBigInteger('province_id')->comment('FK ke master propinsi');
            $table->unsignedBigInteger('city_id')->comment('FK ke master city');
            $table->unsignedBigInteger('district_id')->comment('FK ke master kecamatan');
            $table->unsignedBigInteger('sub_district_id')->comment('FK ke master kelurahan');
            $table->string('post_code', 6);
            $table->string('courier_email', 64);
            $table->string('phone1', 32);
            $table->string('phone2', 32)->nullable();
            $table->string('pic1_name', 255)->comment('nama pic');
            $table->string('pic1_phone', 32);
            $table->string('pic1_email', 64);
            $table->string('npwp_no', 24)->nullable()->comment('no npwp customer');
            $table->string('npwp_address', 1024)->nullable()->comment('npwp office address');
            $table->unsignedBigInteger('npwp_province_id')->nullable()->comment('FK ke master propinsi');
            $table->unsignedBigInteger('npwp_city_id')->nullable()->comment('FK ke master city');
            $table->unsignedBigInteger('npwp_district_id')->nullable()->comment('FK ke master kecamatan');
            $table->unsignedBigInteger('npwp_sub_district_id')->nullable()->comment('FK ke master kelurahan');
            $table->char('active', 1)->default('Y');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by');
            $table->timestamps();

            $table->foreign('entity_type_id')->references('id')->on('mst_globals');
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
        Schema::dropIfExists('mst_couriers');
    }
};
