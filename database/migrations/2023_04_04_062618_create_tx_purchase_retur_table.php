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
        Schema::create('tx_purchase_returs', function (Blueprint $table) {
            $table->id();
            $table->string('purchase_retur_no', 15);
            $table->date('purchase_retur_date');
            $table->unsignedBigInteger('supplier_id')->comment('FK ke master supplier');
            $table->unsignedBigInteger('supplier_type_id')->nullable();
            $table->unsignedBigInteger('supplier_entity_type_id')->nullable();
            $table->string('supplier_name', 255)->nullable();
            $table->unsignedBigInteger('currency_id')->comment('FK ke master currency');
            $table->decimal('exc_rate',20,2)->nullable();
            $table->unsignedBigInteger('branch_id')->comment('FK ke master branches');
            $table->unsignedBigInteger('courier_id')->comment('FK ke master couriers');
            $table->text('remark')->nullable();
            $table->integer('total_qty')->nullable()->default(0);
            $table->decimal('total_before_vat', 20, 2)->default(0)->nullable()->comment('total harga sebelum PPN');
            $table->decimal('total_after_vat', 20, 2)->default(0)->nullable()->comment('total harga sesudah PPN');

            $table->char('active', 1)->default('Y');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by');
            $table->timestamps();

            $table->foreign('supplier_id')->references('id')->on('mst_suppliers');
            $table->foreign('supplier_type_id')->references('id')->on('mst_globals');
            $table->foreign('supplier_entity_type_id')->references('id')->on('mst_globals');
            $table->foreign('currency_id')->references('id')->on('mst_globals');
            $table->foreign('branch_id')->references('id')->on('mst_branches');
            $table->foreign('courier_id')->references('id')->on('mst_couriers');
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
        Schema::dropIfExists('tx_purchase_retur');
    }
};
