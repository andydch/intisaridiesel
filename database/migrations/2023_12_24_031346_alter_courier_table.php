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
        Schema::table('tx_sales_orders', function (Blueprint $table) {
            $table->integer('courier_type')->nullable()->after('courier_id')->comment('ambil sendiri/diantar/kurir');
        });

        Schema::table('tx_surat_jalans', function (Blueprint $table) {
            $table->integer('courier_type')->nullable()->after('courier_id')->comment('ambil sendiri/diantar/kurir');
        });

        Schema::table('tx_purchase_returs', function (Blueprint $table) {
            $table->integer('courier_type')->nullable()->after('courier_id')->comment('ambil sendiri/diantar/kurir');
        });

        Schema::table('tx_receipt_orders', function (Blueprint $table) {
            $table->integer('courier_type')->nullable()->after('courier_id')->comment('ambil sendiri/diantar/kurir');
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
