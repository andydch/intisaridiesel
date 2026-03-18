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
        Schema::create('mst_courier_bank_information', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('courier_id')->comment('FK ke master courier');
            $table->string('bank_name', 255);
            $table->string('bank_address', 1024);
            $table->string('account_name', 255);
            $table->string('account_no', 255);
            $table->unsignedBigInteger('currency_id')->comment('FK ke master global - currency');
            $table->string('swift_code', 255);
            $table->string('bsb_code', 255);
            $table->char('active', 1)->default('Y');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by');
            $table->timestamps();

            $table->foreign('courier_id')->references('id')->on('mst_couriers');
            $table->foreign('currency_id')->references('id')->on('mst_globals');
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
        Schema::dropIfExists('mst_courier_bank_information');
    }
};
