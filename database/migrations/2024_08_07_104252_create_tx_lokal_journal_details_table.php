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
        Schema::create('tx_lokal_journal_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lokal_journal_id')->comment('FK ke master lokal journal');
            $table->unsignedBigInteger('coa_id')->comment('FK ke master coa');
            $table->text('description',1000)->nullable();
            $table->decimal('debit',20,2)->default(0)->comment('debit');
            $table->decimal('kredit',20,2)->default(0)->comment('kredit');
            $table->decimal('debit_old',20,2)->nullable()->comment('simpan nilai debet lama');
            $table->decimal('kredit_old',20,2)->nullable()->comment('simpan nilai kredit lama');
            $table->decimal('debit_new',20,2)->nullable()->comment('simpan nilai debet baru');
            $table->decimal('kredit_new',20,2)->nullable()->comment('simpan nilai kredit baru');
            $table->char('active', 1)->default('Y');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by');
            $table->timestamps();

            $table->foreign('lokal_journal_id')->references('id')->on('tx_lokal_journals');
            // $table->foreign('coa_id')->references('id')->on('mst_coas');
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
        Schema::dropIfExists('tx_lokal_journal_details');
    }
};
