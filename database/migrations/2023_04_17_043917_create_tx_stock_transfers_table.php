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
        Schema::create('tx_stock_transfers', function (Blueprint $table) {
            $table->id();
            $table->string('stock_transfer_no', 15);
            $table->date('stock_transfer_date');
            $table->unsignedBigInteger('branch_from_id')->comment('FK ke master branch');
            $table->unsignedBigInteger('branch_to_id')->comment('FK ke master branch');
            $table->text('remark')->nullable();
            $table->char('active', 1)->default('Y');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->dateTime('approved_at')->nullable();
            $table->unsignedBigInteger('canceled_by');
            $table->dateTime('canceled_at')->nullable();
            $table->unsignedBigInteger('received_by');
            $table->dateTime('received_at')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by');
            $table->timestamps();

            $table->foreign('branch_from_id')->references('id')->on('mst_branches');
            $table->foreign('branch_to_id')->references('id')->on('mst_branches');
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('updated_by')->references('id')->on('users');
            $table->foreign('approved_by')->references('id')->on('users');
            $table->foreign('canceled_by')->references('id')->on('users');
            $table->foreign('received_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tx_stock_transfers');
    }
};
