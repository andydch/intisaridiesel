<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('tx_piutang_tmp', function (Blueprint $table) {
            $table->id();
            $table->string('kode_customer', 20); // kolom A
            $table->unsignedBigInteger('inv_or_kwi_id'); // ID INV atau KWI
            $table->string('tipe_invoice', 1); // I/K
            $table->decimal('dpp', 15, 2); // D
            $table->decimal('ppn', 15, 2); // E
            $table->decimal('total', 15, 2); // F
            $table->string('journal_type', 1); // G
            $table->unsignedBigInteger('coa_id'); // J
            $table->unsignedTinyInteger('metode_bayar_id'); // H
            $table->unsignedBigInteger('bayar_via_id')->nullable(); // I
            $table->string('no_giro', 50)->nullable(); // K
            $table->date('jurnal_date')->nullable(); // L boleh kosong
            $table->decimal('discount', 15, 2)->default(0); // M waris
            $table->decimal('admin_bank', 15, 2)->default(0); // N waris
            $table->decimal('penerimaan_lain', 15, 2)->default(0); // O waris
            $table->decimal('biaya_kirim', 15, 2)->default(0); // P waris
            $table->timestamps();
            $table->index('kode_customer');
            $table->index('tipe_invoice');
        });
    }
    public function down(): void {
        Schema::dropIfExists('tx_piutang_tmp');
    }
};
