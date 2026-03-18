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
        Schema::create('mst_part_brand_types', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('part_id')->comment('FK ke master parts');
            $table->unsignedBigInteger('brand_id')->comment('FK ke master globals');
            $table->unsignedBigInteger('brand_type_id')->comment('FK ke master brand types');
            $table->char('active', 1)->default('Y');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by');
            $table->timestamps();

            $table->foreign('part_id')->references('id')->on('mst_parts');
            $table->foreign('brand_id')->references('id')->on('mst_globals');
            $table->foreign('brand_type_id')->references('id')->on('mst_brand_types');
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
        Schema::dropIfExists('mst_part_brand_types');
    }
};
