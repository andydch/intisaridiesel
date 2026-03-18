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
        Schema::create('tx_delivery_order_non_taxes', function (Blueprint $table) {
            $table->id();
            $table->string('delivery_order_no',15);
            $table->date('delivery_order_date');
            $table->date('do_expired_date')->nullable();
            $table->string('sales_order_no_all',512)->nullable();
            $table->unsignedBigInteger('tax_invoice_id')->nullable()->comment('FK ke tax invoice');
            $table->unsignedBigInteger('customer_id')->comment('FK ke master customer');
            $table->unsignedBigInteger('customer_entity_type_id');
            $table->string('customer_name',255);
            $table->unsignedBigInteger('c_shipment_addr_id')->nullable()->comment('FK ke mst_customer_shipment_address');
            $table->unsignedBigInteger('courier_id')->nullable()->comment('FK ke mst_couriers');
            $table->text('remark')->nullable();
            $table->unsignedBigInteger('branch_id')->nullable()->comment('FK ke mst_branches');
            $table->integer('total_qty')->default(0)->nullable();
            $table->decimal('total_price',20,2)->default(0)->nullable();
            $table->char('is_draft',1)->nullable()->default('N');
            $table->dateTime('draft_at')->nullable();
            $table->dateTime('draft_to_created_at')->nullable();
            $table->char('active',1)->default('Y');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by');
            $table->timestamps();

            $table->foreign('tax_invoice_id')->references('id')->on('tx_tax_invoices');
            $table->foreign('customer_id')->references('id')->on('mst_customers');
            $table->foreign('c_shipment_addr_id')->references('id')->on('mst_customer_shipment_address');
            $table->foreign('courier_id')->references('id')->on('mst_couriers');
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
        Schema::dropIfExists('tx_delivery_order_non_taxes');
    }
};
