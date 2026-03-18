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
        Schema::create('tx_delivery_order_non_tax_parts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('delivery_order_id')->comment('FK ke tx_delivery_order_non_taxes');
            $table->unsignedBigInteger('sales_order_id')->comment('FK ke tx_sales_orders');
            $table->unsignedBigInteger('sales_order_part_id')->comment('FK ke tx_sales_order_parts');
            $table->unsignedBigInteger('part_id')->comment('FK ke mst_parts');
            $table->integer('qty');
            $table->integer('qty_so');
            $table->decimal('final_price',20,2);
            $table->decimal('total_price',20,2);
            $table->string('description',1024)->nullable();
            $table->char('is_partial_delivered',1)->default('N');
            $table->char('active',1)->default('Y');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by');
            $table->timestamps();

            $table->foreign('delivery_order_id')->references('id')->on('tx_delivery_order_non_taxes');
            $table->foreign('sales_order_id')->references('id')->on('tx_sales_orders');
            $table->foreign('sales_order_part_id')->references('id')->on('tx_sales_order_parts');
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
        Schema::dropIfExists('tx_delivery_order_non_tax_parts');
    }
};
