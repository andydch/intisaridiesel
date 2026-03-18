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
        Schema::create('tx_stock_assembly_parts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('stock_assembly_id')->comment('FK ke table tx_stock_assemblys');
            $table->unsignedBigInteger('part_id')->comment('FK ke table master part');
            $table->integer('qty');
            $table->decimal('final_cost',20,2);
            $table->decimal('avg_cost',20,2);
            $table->char('active', 1)->default('Y');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by');
            $table->timestamps();

            $table->foreign('stock_assembly_id')->references('id')->on('tx_stock_assemblys');
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
        Schema::dropIfExists('tx_stock_assembly_parts');
    }
};
