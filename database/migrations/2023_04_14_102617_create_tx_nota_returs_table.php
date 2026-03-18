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
        Schema::create('tx_nota_returs', function (Blueprint $table) {
            $table->id();
            $table->string('nota_retur_no', 15);
            $table->date('nota_retur_date');
            $table->unsignedBigInteger('customer_id')->comment('FK ke master customer');
            $table->unsignedBigInteger('customer_entity_type_id');
            $table->string('customer_name', 255);
            $table->unsignedBigInteger('branch_id')->comment('FK ke master branches');
            $table->text('remark')->nullable();
            $table->integer('total_qty')->nullable()->default(0);
            $table->decimal('total_before_vat', 20, 2)->default(0)->nullable()->comment('total harga sebelum PPN');
            $table->decimal('total_after_vat', 20, 2)->default(0)->nullable()->comment('total harga sesudah PPN');
            $table->char('is_vat', 1)->default('N');
            $table->char('active', 1)->default('Y');
            $table->unsignedBigInteger('approved_by');
            $table->unsignedBigInteger('canceled_by');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by');
            $table->timestamps();

            $table->foreign('customer_id')->references('id')->on('mst_customers');
            $table->foreign('branch_id')->references('id')->on('mst_branches');
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('updated_by')->references('id')->on('users');
            $table->foreign('approved_by')->references('id')->on('users');
            $table->foreign('canceled_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tx_nota_returs');
    }
};
