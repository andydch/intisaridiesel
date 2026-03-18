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
        Schema::create('tx_purchase_retur_parts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('purchase_retur_id')->comment('FK ke table tx_purchase_returs');
            $table->unsignedBigInteger('part_id')->comment('FK ke table master part');
            $table->integer('qty');
            $table->integer('qty_retur');
            $table->decimal('final_cost',20,2);
            $table->decimal('total_retur',20,2);
            $table->decimal('total_price',20,2);
            $table->string('description',1024)->nullable();

            $table->char('active', 1)->default('Y');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by');
            $table->timestamps();

            $table->foreign('purchase_retur_id')->references('id')->on('tx_purchase_returs');
            $table->foreign('part_id')->references('id')->on('mst_parts');
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
        Schema::dropIfExists('tx_purchase_retur_parts');
    }
};
