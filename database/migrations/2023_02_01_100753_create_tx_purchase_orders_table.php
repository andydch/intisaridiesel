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
        Schema::create('tx_purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('purchase_no', 10)->comment('no purchase - unique');
            $table->date('purchase_date');
            $table->unsignedBigInteger('supplier_id')->comment('FK ke table master supplier');
            $table->integer('pic_idx')->nullable()->comment('1: PIC #1; 2: PIC #2');
            $table->unsignedBigInteger('currency_id')->comment('FK ke master currency, ambil dari master supplier bank info');
            $table->unsignedBigInteger('branch_id')->comment('FK ke table master branch');
            $table->unsignedBigInteger('company_id')->comment('FK ke table master company');
            $table->char('active', 1)->default('Y');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by');
            $table->timestamps();

            $table->unique('purchase_no');
            $table->foreign('supplier_id')->references('id')->on('mst_suppliers');
            $table->foreign('currency_id')->references('id')->on('mst_globals');
            $table->foreign('branch_id')->references('id')->on('mst_branches');
            $table->foreign('company_id')->references('id')->on('mst_companies');
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
        Schema::dropIfExists('tx_purchase_orders');
    }
};
