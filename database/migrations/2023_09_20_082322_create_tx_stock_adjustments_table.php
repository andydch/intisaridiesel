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
        Schema::create('tx_stock_adjustments', function (Blueprint $table) {
            $table->id();
            $table->string('stock_adj_no',15)->comment('no stock adjustment');
            $table->unsignedBigInteger('branch_id')->comment('FK ke mst_branches');
            $table->text('remark')->nullable();
            $table->decimal('total',20,2);
            $table->char('is_draft',1)->nullable();
            $table->dateTime('draft_at')->nullable();
            $table->dateTime('draft_to_created_at')->nullable();
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
        Schema::dropIfExists('tx_stock_adjustments');
    }
};
