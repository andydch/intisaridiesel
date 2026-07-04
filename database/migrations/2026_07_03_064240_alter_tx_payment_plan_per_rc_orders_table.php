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
        Schema::table('tx_payment_plan_per_rc_orders', function (Blueprint $table) {
            $table->unsignedBigInteger('supplier_id')->nullable()->after('payment_plan_id')->comment('ID supplier');
            $table->unsignedBigInteger('tagihan_supplier_id')->nullable()->after('supplier_id')->comment('ID tagihan supplier');
            $table->string('tagihan_supplier_no')->nullable()->after('tagihan_supplier_id')->comment('Nomor tagihan supplier');
            $table->unsignedBigInteger('payment_voucher_id')->nullable()->after('plan_pay')->comment('ID voucher pembayaran');
            $table->string('payment_voucher_no')->nullable()->after('payment_voucher_id')->comment('Nomor voucher pembayaran');
            $table->date('actual_date')->nullable()->after('payment_voucher_no')->comment('Tanggal actual penerimaan pembayaran');
            $table->decimal('actual_payment', 20, 2)->nullable()->after('actual_date')->comment('Jumlah actual penerimaan pembayaran');

            $table->foreign('supplier_id')->references('id')->on('mst_suppliers');
            $table->foreign('tagihan_supplier_id')->references('id')->on('tx_tagihan_suppliers');
            $table->foreign('payment_voucher_id')->references('id')->on('tx_payment_vouchers');

            $table->dropForeign('tx_payment_plan_per_rc_orders_receipt_order_id_foreign');
            $table->dropColumn('receipt_order_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
};
