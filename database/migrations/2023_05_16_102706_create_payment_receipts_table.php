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
        Schema::create('tx_payment_receipts', function (Blueprint $table) {
            $table->id();
            $table->string('payment_receipt_no',15);
            $table->unsignedBigInteger('customer_id')->comment('FK ke master customer');
            $table->date('payment_date')->comment('tanggal pembayaran');
            $table->decimal('payment_total',20,2)->comment('total pembayaran');
            $table->unsignedBigInteger('coa_id')->comment('FK ke master COA - Lvl 3');
            $table->unsignedBigInteger('payment_reference_id')->comment('FK ke master global - payment reference');
            $table->string('reference_no',255)->comment('no referensi pembayaran');
            $table->date('reference_date')->comment('tanggal no reference');
            $table->char('is_full_payment',1)->default('Y')->nullable()->comment('jika ada pembayaran belum 100%');
            $table->text('remark')->nullable();
            // $table->unsignedBigInteger('approved_by')->nullable();
            // $table->dateTime('approved_at')->nullable();
            // $table->unsignedBigInteger('canceled_by');
            // $table->dateTime('canceled_at')->nullable();
            $table->dateTime('draft_at')->nullable();
            $table->dateTime('draft_to_created_at')->nullable();
            $table->char('is_draft',1)->default('N')->nullable();
            $table->char('active', 1)->default('Y');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by');
            $table->timestamps();

            $table->foreign('customer_id')->references('id')->on('mst_customers');
            $table->foreign('coa_id')->references('id')->on('mst_coas');
            // $table->foreign('approved_by')->references('id')->on('users');
            // $table->foreign('canceled_by')->references('id')->on('users');
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
        Schema::dropIfExists('payment_receipts');
    }
};
