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
        Schema::table('tx_nota_returs', function (Blueprint $table) {
            $table->unsignedBigInteger('approved_by')->nullable()->change();
            $table->unsignedBigInteger('canceled_by')->nullable()->change();
            $table->dateTime('approved_at')->nullable()->after('approved_by');
            $table->dateTime('canceled_at')->nullable()->after('canceled_by');
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
