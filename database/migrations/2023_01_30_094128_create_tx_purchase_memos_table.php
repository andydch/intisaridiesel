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
        Schema::create('tx_purchase_memos', function (Blueprint $table) {
            $table->id();
            $table->string('memo_no', 10)->comment('no memo - unique');
            $table->date('memo_date');
            $table->unsignedBigInteger('supplier_id')->comment('FK ke table master supplier');
            $table->integer('pic_idx')->nullable()->comment('1: PIC #1; 2: PIC #2');
            $table->unsignedBigInteger('branch_id')->comment('FK ke table master branch');
            $table->char('active', 1)->default('Y');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by');
            $table->timestamps();

            $table->unique('memo_no');
            $table->foreign('supplier_id')->references('id')->on('mst_suppliers');
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
        Schema::dropIfExists('tx_purchase_memos');
    }
};
