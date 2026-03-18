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
        Schema::create('mst_salesman_target_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('salesman_target_id')->comment('FK ke mst_salesman_targets');
            $table->unsignedBigInteger('salesman_id')->comment('FK ke userdetails');
            $table->integer('year_per_branch')->comment('tahun dari sales target tiap cabang');
            $table->decimal('sales_target_per_branch',20,2)->comment('total target sales per tahun per branch');
            $table->char('active',1)->default('Y');
            $table->unsignedBigInteger('created_by')->comment('FK ke users');
            $table->unsignedBigInteger('updated_by')->comment('FK ke users');
            $table->timestamps();

            $table->foreign('salesman_target_id')->references('id')->on('mst_salesman_targets');
            $table->foreign('salesman_id')->references('id')->on('userdetails');
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
        Schema::dropIfExists('mst_salesman_target_details');
    }
};
