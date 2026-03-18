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
        Schema::create('tx_purchase_order_oo_oh_parts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('purchase_order_id')->comment('FK ke table tx_purchase_orders');
            $table->unsignedBigInteger('part_id')->comment('FK ke table mst_parts');
            $table->unsignedBigInteger('branch_id')->comment('FK ke table mst_branches');
            $table->integer('last_OO_PO_created')->comment('status OO terakhir setelah no PO terbentuk');
            $table->integer('last_OH_PO_created')->comment('status OH terakhir setelah no PO terbentuk');
            $table->dateTime('last_OO_OH_PO_created')->comment('tanggal/jam status OO/OH terakhir');
            $table->integer('last_OO_PO_approval')->nullable()->default(0)->comment('status OO terakhir setelah approve/reject');
            $table->integer('last_OH_PO_approval')->nullable()->default(0)->comment('status OH terakhir setelah approve/reject');
            $table->dateTime('last_OO_OH_PO_approval')->nullable()->comment('tanggal/jam status OO/OH setelah approve/reject');
            $table->char('active', 1)->default('Y');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by');
            $table->timestamps();

            $table->foreign('purchase_order_id')->references('id')->on('tx_purchase_orders');
            $table->foreign('part_id')->references('id')->on('mst_parts');
            $table->foreign('branch_id')->references('id')->on('mst_branches');
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
        Schema::dropIfExists('tx_purchase_order_oo_oh_parts_tables');
    }
};
