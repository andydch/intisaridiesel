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
        Schema::create('tx_acceptance_plans', function (Blueprint $table) {
            $table->id();
            $table->date('acceptance_month')->comment('bulan penerimaan');
            $table->char('is_draft',1)->nullable();
            $table->dateTime('draft_at')->nullable();
            $table->dateTime('draft_to_created_at')->nullable();
            $table->char('active', 1)->default('Y');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by');
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('updated_by')->references('id')->on('users');
        });

        Schema::create('tx_acceptance_plan_per_invoices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('acceptance_plan_id')->comment('FK ke tx_acceptance_plans');
            $table->date('plan_date')->comment('tanggal rencana terima');
            $table->decimal('plan_accept',20,2)->comment('rencana pembayaran yang mau diterima');
            $table->unsignedBigInteger('inv_or_kwi_id')->comment('berisi ID invoice atau kwitansi');
            $table->char('inv_or_kwi', 1)->comment('penanda invoice atau kwitansi');
            $table->char('active', 1)->default('Y');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by');
            $table->timestamps();

            $table->foreign('acceptance_plan_id')->references('id')->on('tx_acceptance_plans');
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
        Schema::dropIfExists('tx_acceptance_plans');
        Schema::dropIfExists('tx_acceptance_plan_per_invoices');
    }
};
