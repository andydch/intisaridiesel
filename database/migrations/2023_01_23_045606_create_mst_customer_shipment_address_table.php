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
        Schema::create('mst_customer_shipment_address', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id')->comment('FK ke master customer');
            $table->string('address', 1024)->comment('office address');
            $table->unsignedBigInteger('province_id')->comment('FK ke master propinsi');
            $table->unsignedBigInteger('city_id')->comment('FK ke master city');
            $table->unsignedBigInteger('district_id')->comment('FK ke master kecamatan');
            $table->unsignedBigInteger('sub_district_id')->comment('FK ke master kelurahan');
            $table->string('phone', 32);
            $table->timestamps();

            $table->foreign('customer_id')->references('id')->on('mst_customers');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mst_customer_shipment_address');
    }
};
