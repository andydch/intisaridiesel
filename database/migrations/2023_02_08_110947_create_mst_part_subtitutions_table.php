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
        Schema::create('mst_part_subtitutions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('part_id')->comment('FK ke master parts');
            $table->unsignedBigInteger('part_other_id')->comment('FK ke master parts utk part lain');
            $table->char('active', 1)->default('Y');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by');
            $table->timestamps();

            $table->foreign('part_id')->references('id')->on('mst_parts');
            $table->foreign('part_other_id')->references('id')->on('mst_parts');
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
        Schema::dropIfExists('mst_part_subtitutions');
    }
};
