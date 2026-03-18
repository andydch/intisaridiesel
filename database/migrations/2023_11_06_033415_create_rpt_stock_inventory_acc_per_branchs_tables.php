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
        Schema::create('rpt_stock_inventory_acc_per_branchs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_id')->comment('FK ke master_branches');
            $table->integer('rpt_month')->comment('data dari bulan berjalan');
            $table->integer('rpt_year')->comment('data dari tahun berjalan');
            $table->decimal('purchase_in',22,2)->comment('receipt order dikurangi purchase retur');
            $table->decimal('sales_out',22,2)->comment('FK+NP - NR+RE');
            $table->decimal('end_stock',22,2)->comment('beginning_stock + purchase_in - sales_out');
            $table->decimal('actual_stock',22,2)->comment('sesuai dengan laporan inventory');
            $table->char('active',1)->default('Y');
            $table->unsignedBigInteger('created_by')->comment('FK ke users');
            $table->unsignedBigInteger('updated_by')->comment('FK ke users');
            $table->timestamps();

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
        Schema::dropIfExists('rpt_stock_inventory_acc_per_branchs_tables');
    }
};
