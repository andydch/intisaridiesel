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
        Schema::create('tx_receipt_orders', function (Blueprint $table) {
            $table->id();
            $table->string('receipt_no', 15)->comment('receipt order no: RO0001-01-2023, 01 -> kode cabang');
            $table->date('receipt_date');
            $table->string('po_or_pm_no', 12)->comment('no purchase order atau no purchase memo');
            $table->string('supplier_doc_no', 255);
            $table->unsignedBigInteger('supplier_id')->comment('FK ke master supplier');
            $table->unsignedBigInteger('supplier_type_id')->nullable();
            $table->unsignedBigInteger('supplier_entity_type_id')->nullable();
            $table->string('supplier_name', 255)->nullable();
            $table->unsignedBigInteger('currency_id')->comment('FK ke master currency');
            $table->integer('total_qty')->nullable()->default(0);
            $table->decimal('total_before_vat', 20, 2)->default(0)->nullable()->comment('total harga sebelum PPN');
            $table->decimal('total_after_vat', 20, 2)->default(0)->nullable()->comment('total harga sesudah PPN');
            $table->unsignedBigInteger('branch_id')->comment('FK ke master branches');
            $table->unsignedBigInteger('company_id')->comment('FK ke master companies');

            $table->char('active', 1)->default('Y');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by');
            $table->timestamps();

            $table->foreign('supplier_id')->references('id')->on('mst_suppliers');
            $table->foreign('supplier_type_id')->references('id')->on('mst_globals');
            $table->foreign('supplier_entity_type_id')->references('id')->on('mst_globals');
            $table->foreign('currency_id')->references('id')->on('mst_globals');
            $table->foreign('branch_id')->references('id')->on('mst_branches');
            // $table->foreign('company_id')->references('id')->on('mst_companies');
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
        Schema::dropIfExists('tx_receipt_orders');
    }
};
