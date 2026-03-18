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
        Schema::create('tx_surat_jalan_parts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('surat_jalan_id')->comment('FK ke tx_surat_jalans');
            $table->unsignedBigInteger('part_id')->comment('FK ke mst_parts');
            $table->integer('qty');
            $table->decimal('price',20,2);
            $table->string('desc',1024)->nullable();
            $table->char('active',1)->default('Y');
            $table->unsignedBigInteger('created_by')->comment('FK ke users');
            $table->unsignedBigInteger('updated_by')->comment('FK ke users');
            $table->timestamps();

            $table->foreign('surat_jalan_id')->references('id')->on('tx_surat_jalans');
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
        Schema::dropIfExists('tx_surat_jalan_parts');
    }
};
