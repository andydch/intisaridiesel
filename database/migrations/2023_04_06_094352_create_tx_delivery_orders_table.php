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
        Schema::create('tx_delivery_orders', function (Blueprint $table) {
            $table->id();
            $table->string('delivery_order_no', 15);
            $table->date('delivery_order_date');
            $table->unsignedBigInteger('customer_id')->comment('FK ke master customer');
            $table->unsignedBigInteger('customer_entity_type_id');
            $table->string('customer_name', 255);
            $table->unsignedBigInteger('c_shipment_addr_id')->comment('customer shipment address');
            $table->unsignedBigInteger('courier_id')->comment('FK ke master couriers');
            $table->text('remark')->nullable();
            $table->integer('total_qty')->nullable()->default(0);
            $table->decimal('total_before_vat', 20, 2)->default(0)->nullable()->comment('total harga sebelum PPN');
            $table->decimal('total_after_vat', 20, 2)->default(0)->nullable()->comment('total harga sesudah PPN');
            $table->char('is_vat', 1)->default('N');
            $table->char('active', 1)->default('Y');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by');
            $table->unsignedBigInteger('adjustment_by');
            $table->timestamps();

            $table->foreign('customer_id')->references('id')->on('mst_customers');
            $table->foreign('c_shipment_addr_id')->references('id')->on('mst_customer_shipment_address');
            $table->foreign('courier_id')->references('id')->on('mst_couriers');
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('updated_by')->references('id')->on('users');
            $table->foreign('adjustment_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tx_delivery_orders');
    }
};
