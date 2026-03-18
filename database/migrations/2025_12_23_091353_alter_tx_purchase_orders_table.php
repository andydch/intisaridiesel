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
        Schema::table('tx_purchase_orders', function (Blueprint $table) {
            $table->unsignedBigInteger('director_updated_by')->nullable()->comment('FK ke master user - jika direktur ubah data')->after('vat_val');
            $table->dateTime('director_updated_at')->nullable()->comment('Tanggal dan waktu direktur ubah data')->after('director_updated_by');
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
