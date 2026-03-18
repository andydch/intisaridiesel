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
        Schema::create('tx_stock_adjustment_parts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('stock_adj_id')->comment('FK ke tx_stock_adjustments');
            $table->unsignedBigInteger('part_id')->comment('FK ke mst_parts');
            $table->integer('adjustment')->default(0)->comment('nilai penyesuaian, +/-');
            $table->integer('qty_oh')->default(0)->comment('on hand qty');
            $table->integer('qty_oh_adjustment')->default(0)->comment('on hand qty setelah disesuaikan');
            $table->integer('qty_so')->default(0)->comment('sales order qty');
            $table->decimal('avg_cost',20,2)->default(0)->comment('avg cost per part');
            $table->decimal('total',20,2)->default(0)->comment('adjustment * avg cost');
            $table->char('active',1)->default('Y');
            $table->unsignedBigInteger('created_by')->comment('FK ke users');
            $table->unsignedBigInteger('updated_by')->comment('FK ke users');
            $table->timestamps();

            $table->foreign('stock_adj_id')->references('id')->on('tx_stock_adjustments');
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
        Schema::dropIfExists('tx_stock_adjustment_parts');
    }
};
