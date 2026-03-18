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
        Schema::create('tx_kwitansis', function (Blueprint $table) {
            $table->id();
            $table->string('kwitansi_no',15)->comment('no kwitansi, contoh: KWXX-XXXXX');
            $table->unsignedBigInteger('customer_id')->comment('FK ke mst_customers');
            $table->date('kwitansi_date');
            $table->dateTime('kwitansi_expired_date')->comment('exp date dari nota penjualan terakhir');
            $table->decimal('np_total',20,2)->default(0);
            $table->text('header')->nullable();
            $table->text('footer')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->dateTime('approved_at')->nullable();
            $table->unsignedBigInteger('canceled_by')->nullable();
            $table->dateTime('canceled_at')->nullable();
            $table->dateTime('draft_at')->nullable();
            $table->dateTime('draft_to_created_at')->nullable();
            $table->char('is_draft',1)->default('N');
            $table->char('active',1)->default('Y');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->foreign('customer_id')->references('id')->on('mst_customers');
            $table->foreign('approved_by')->references('id')->on('users');
            $table->foreign('canceled_by')->references('id')->on('users');
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
        Schema::dropIfExists('tx_kwitansis');
    }
};
