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
        Schema::create('tx_purchase_inquiry_parts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('purchase_inquiry_id')->comment('FK ke table tx_purchase_inquiries');
            $table->string('part_name',255)->comment('keterangan part yg akan diproses');
            $table->integer('qty');
            $table->string('unit',16)->nullable();
            $table->string('description',1024)->nullable();

            $table->char('active', 1)->default('Y');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by');
            $table->timestamps();

            $table->foreign('purchase_inquiry_id')->references('id')->on('tx_purchase_inquiries');
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
        Schema::dropIfExists('tx_purchase_inquiry_parts');
    }
};
