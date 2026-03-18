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
        Schema::create('tx_tagihan_suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('tagihan_supplier_no',15)->comment('Nomor Tagihan Supplier');
            $table->date('tagihan_supplier_date')->comment('Tanggal Tagihan Supplier');
            $table->foreignId('supplier_id')->constrained('mst_suppliers')->onDelete('cascade');
            $table->decimal('total_price', 20, 2)->default(0)->comment('Total Harga');
            $table->decimal('total_price_vat', 20, 2)->default(0)->comment('Total VAT jika RO yg dipilih VAT');
            $table->decimal('grandtotal_price', 20, 2)->default(0)->comment('Grand Total Harga');
            $table->char('is_vat', 1)->nullable()->default('N')->comment('Apakah PPN? Y jika Ya, N jika Tidak');
            $table->unsignedBigInteger('bank_id')->comment('ID Bank, FK ke mst_coas');
            $table->char('active', 1)->default('Y');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by');
            $table->timestamps();
            
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('updated_by')->references('id')->on('users');
        });
        
        Schema::create('tx_tagihan_supplier_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tagihan_supplier_id')->constrained('tx_tagihan_suppliers')->onDelete('cascade');
            $table->foreignId('receipt_order_id')->constrained('tx_receipt_orders')->onDelete('cascade');
            $table->decimal('total_price_per_ro', 20, 2)->default(0)->comment('Total Harga per RO');
            $table->char('is_vat_per_ro', 1)->nullable()->default('N')->comment('Apakah PPN per RO? Y jika Ya, N jika Tidak');
            $table->char('active', 1)->default('Y');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by');
            $table->timestamps();

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
        Schema::dropIfExists('tx_tagihan_suppliers');
        Schema::dropIfExists('tx_tagihan_supplier_details');
    }
};
