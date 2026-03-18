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
        Schema::create('tx_lokal_journals', function (Blueprint $table) {
            $table->id();
            $table->string('general_journal_no',15);
            $table->date('general_journal_date')->comment('tanggal jurnal');
            $table->decimal('total_debit',20,2)->default(0)->comment('total debit');
            $table->decimal('total_kredit',20,2)->default(0)->comment('total kredit');
            $table->string('module_no',15)->nullable();
            $table->unsignedBigInteger('automatic_journal_id')->nullable();
            $table->dateTime('draft_at')->nullable();
            $table->dateTime('draft_to_created_at')->nullable();
            $table->char('is_draft',1)->default('N')->nullable();
            $table->char('is_wt_for_appr',1)->default('N')->nullable()->comment('waiting for approval Y/N');
            $table->unsignedBigInteger('who_appr')->nullable()->comment('yg beri approval');
            $table->char('status_appr',1)->nullable()->comment('status approval apakah Y/N/NULL');
            $table->date('general_journal_date_old')->nullable()->comment('tgl jurnal sebelum diubah');
            $table->decimal('total_debit_old',20,2)->nullable()->comment('simpan nilai debet lama');
            $table->decimal('total_kredit_old',20,2)->nullable()->comment('simpan nilai kredit lama');
            $table->decimal('total_debit_new',20,2)->nullable()->comment('simpan nilai debet baru');
            $table->decimal('total_kredit_new',20,2)->nullable()->comment('simpan nilai kredit baru');
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
        Schema::dropIfExists('tx_lokal_journals');
    }
};
