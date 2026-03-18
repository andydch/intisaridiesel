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
        Schema::create('mst_coas', function (Blueprint $table) {
            $table->id();
            $table->integer('coa_level')->comment('pilihan value 1/2/3/4/5');
            $table->integer('coa_code');
            $table->string('coa_name', 255);
            $table->integer('coa_parent')->comment('berisi coa level di atas nya');
            $table->char('is_master_coa')->default('N')->nullable()->comment('jika N maka tdk dpt di-jurnal');
            $table->char('is_balance_sheet')->default('N')->nullable()->comment('jika N maka tdk muncul di laporan Neraca (Balance Sheet)');
            $table->char('is_profit_loss')->default('N')->nullable()->comment('jika N maka tdk muncul di laporan Neraca (Balance Sheet)');

            $table->char('active', 1)->default('Y');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by');
            $table->timestamps();

            $table->unique('coa_code');
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
        Schema::dropIfExists('mst_coas');
    }
};
