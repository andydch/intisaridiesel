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
        Schema::create('tx_nota_retur_part_non_taxes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('nota_retur_id')->comment('FK ke tx_nota_retur_non_taxes');
            $table->unsignedBigInteger('surat_jalan_part_id')->comment('FK ke tx_surat_jalan_parts');
            $table->unsignedBigInteger('part_id')->comment('FK ke mst_parts');
            $table->integer('qty_retur')->comment('qty yg di retur');
            $table->integer('qty_do')->comment('qty yg di surat jalan');
            $table->decimal('final_price',20,2)->default(0);
            $table->decimal('total_price',20,2)->default(0);
            $table->string('description',512)->nullable();
            $table->char('active',1)->default('Y');
            $table->unsignedBigInteger('created_by')->comment('FK ke users');
            $table->unsignedBigInteger('updated_by')->comment('FK ke users');
            $table->timestamps();

            $table->foreign('nota_retur_id')->references('id')->on('tx_nota_retur_non_taxes');
            $table->foreign('surat_jalan_part_id')->references('id')->on('tx_surat_jalan_parts');
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
        Schema::dropIfExists('tx_nota_retur_part_non_taxes');
    }
};
