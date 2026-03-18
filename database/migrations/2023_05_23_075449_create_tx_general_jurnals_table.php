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
        Schema::create('tx_general_journals', function (Blueprint $table) {
            $table->id();
            $table->string('general_journal_no',15);
            $table->date('general_journal_date')->comment('tanggal jurnal');
            $table->decimal('total_debit',20,2)->default(0)->comment('total debit');
            $table->decimal('total_kredit',20,2)->default(0)->comment('total kredit');
            $table->dateTime('draft_at')->nullable();
            $table->dateTime('draft_to_created_at')->nullable();
            $table->char('is_draft',1)->default('N')->nullable();
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
        Schema::dropIfExists('tx_general_jurnals');
    }
};
