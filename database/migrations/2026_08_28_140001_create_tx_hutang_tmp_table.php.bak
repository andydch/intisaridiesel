<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('tx_hutang_tmp', function (Blueprint $table) {
            $table->id();
            $table->string('kode_supplier', 20); // kolom A
            $table->unsignedBigInteger('cts_id')->unique(); // ID CTS baru, unique
            $table->string('journal_type', 1); // P/N kolom C
            $table->decimal('total', 15, 2); // grandtotal_price dari tx_tagihan_suppliers
            $table->unsignedBigInteger('branch_id'); // K
            $table->unsignedBigInteger('coa_id'); // M bank_id
            $table->unsignedTinyInteger('metode_bayar_id'); // O -> 1|2|3
            $table->unsignedBigInteger('bayar_via_id')->nullable(); // P -> mst_globals payment-ref
            $table->string('no_giro', 50)->nullable(); // R
            $table->date('jurnal_date')->nullable(); // S boleh kosong
            $table->decimal('admin_bank', 15, 2)->default(0); // T
            $table->decimal('biaya_asuransi', 15, 2)->default(0); // U
            $table->decimal('biaya_kirim', 15, 2)->default(0); // V
            $table->decimal('biaya_lain', 15, 2)->default(0); // W
            $table->decimal('discount', 15, 2)->default(0); // X
            $table->timestamps();
            $table->index('kode_supplier');
            $table->foreign('cts_id')->references('id')->on('tx_tagihan_suppliers')->cascadeOnDelete();
        });
    }
    public function down(): void {
        Schema::dropIfExists('tx_hutang_tmp');
    }
};
