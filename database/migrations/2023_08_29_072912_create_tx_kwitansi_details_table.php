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
        Schema::create('tx_kwitansi_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('kwitansi_id')->comment('FK ke tx_kwitansis');
            $table->unsignedBigInteger('np_id')->comment('FK ke tx_delivery_order_non_taxes');
            $table->string('nota_penjualan_no',15)->comment('no nota penjualan');
            $table->date('delivery_order_date');
            $table->string('sj_no',255)->comment('no surat jalan');
            $table->decimal('total',20,2)->default(0)->nullable();
            $table->char('active',1)->default('Y');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->foreign('kwitansi_id')->references('id')->on('tx_kwitansis');
            $table->foreign('np_id')->references('id')->on('tx_delivery_order_non_taxes');
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
        Schema::dropIfExists('tx_kwitansi_details');
    }
};
