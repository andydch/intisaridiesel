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
        Schema::create('tx_cash_flows', function (Blueprint $table) {
            $table->id();
            $table->string('report_code', 6)->comment('penanda group data');
            $table->integer('row_number')->comment('nomor urut baris');
            $table->integer('col_number')->comment('nomor urut kolom');
            $table->date('period');
            $table->unsignedBigInteger('bank_id')->comment('FK ke master COA');
            $table->string('cell_values', 256)->nullable()->comment('isi cell string/numerik');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tx_cash_flows');
    }
};
