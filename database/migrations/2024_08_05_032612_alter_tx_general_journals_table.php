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
        Schema::table('tx_general_journals', function (Blueprint $table) {
            $table->char('is_wt_for_appr',1)->default('N')->nullable()->after('is_draft')->comment('waiting for approval Y/N');
            $table->unsignedBigInteger('who_appr')->nullable()->after('is_wt_for_appr')->comment('yg beri approval');
            $table->char('status_appr',1)->nullable()->after('who_appr')->comment('status approval apakah Y/N/NULL');
            $table->date('general_journal_date_old')->nullable()->after('status_appr')->comment('tgl jurnal sebelum diubah');
            $table->decimal('total_debit_old',20,2)->nullable()->after('general_journal_date_old')->comment('simpan nilai debet lama');
            $table->decimal('total_kredit_old',20,2)->nullable()->after('total_debit_old')->comment('simpan nilai kredit lama');
            $table->decimal('total_debit_new',20,2)->nullable()->after('total_kredit_old')->comment('simpan nilai debet baru');
            $table->decimal('total_kredit_new',20,2)->nullable()->after('total_debit_new')->comment('simpan nilai kredit baru');
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
