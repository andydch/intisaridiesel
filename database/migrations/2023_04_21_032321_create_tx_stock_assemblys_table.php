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
        Schema::create('tx_stock_assemblys', function (Blueprint $table) {
            $table->id();
            $table->string('stock_assembly_no', 15);
            $table->date('stock_assembly_date');
            $table->unsignedBigInteger('part_id');
            $table->integer('qty');
            $table->decimal('final_cost',20,2);
            $table->decimal('avg_cost',20,2);
            $table->text('remark')->nullable();
            $table->char('active', 1)->default('Y');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by');
            $table->timestamps();

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
        Schema::dropIfExists('tx_stock_assemblys');
    }
};
