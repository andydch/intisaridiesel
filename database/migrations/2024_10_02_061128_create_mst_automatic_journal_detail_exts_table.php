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
        Schema::create('mst_automatic_journal_detail_exts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('auto_journal_id')->comment('FK ke mst_automatic_journals');
            $table->unsignedBigInteger('method_id')->nullable();
            $table->unsignedBigInteger('branch_id')->comment('FK ke mst_branches');
            $table->unsignedBigInteger('coa_code_id')->comment('FK ke mst_coas');
            $table->string('desc',250);
            $table->string('debet_or_credit',6);
            $table->integer('order_no')->default(0);
            $table->char('active',1)->default('Y');
            $table->unsignedBigInteger('created_by')->comment('FK ke users');
            $table->unsignedBigInteger('updated_by')->comment('FK ke users');
            $table->timestamps();

            $table->foreign('branch_id')->references('id')->on('mst_branches');
            $table->foreign('coa_code_id')->references('id')->on('mst_coas');
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
        Schema::dropIfExists('mst_automatic_journal_detail_exts');
    }
};
