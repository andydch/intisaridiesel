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
        Schema::create('mst_coa_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('coa_id')->comment('FK ke mst_coa');
            $table->unsignedBigInteger('branch_id')->comment('FK ke mst_branch');
            $table->string('coa_name',255);
            $table->char('is_tax',1)->nullable()->comment('Y atau N');
            $table->char('active',1)->default('Y');
            $table->unsignedBigInteger('created_by')->comment('FK ke users');
            $table->unsignedBigInteger('updated_by')->comment('FK ke users');
            $table->timestamps();

            $table->foreign('coa_id')->references('id')->on('mst_coas');
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
        Schema::dropIfExists('mst_coa_details');
    }
};
