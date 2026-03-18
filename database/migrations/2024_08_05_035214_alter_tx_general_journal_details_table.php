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
        Schema::table('tx_general_journal_details', function (Blueprint $table) {
            $table->decimal('debit_old',20,2)->nullable()->after('kredit')->comment('simpan nilai debet lama');
            $table->decimal('kredit_old',20,2)->nullable()->after('debit_old')->comment('simpan nilai kredit lama');
            $table->decimal('debit_new',20,2)->nullable()->after('kredit_old')->comment('simpan nilai debet baru');
            $table->decimal('kredit_new',20,2)->nullable()->after('debit_new')->comment('simpan nilai kredit baru');
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
