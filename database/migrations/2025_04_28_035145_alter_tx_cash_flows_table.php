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
        Schema::table('tx_cash_flows', function (Blueprint $table) {
            $table->string('f_color', 10)->nullable()->after('cell_values')->comment('warna font');
            $table->string('b_color', 10)->nullable()->after('f_color')->comment('warna latar belakang cell');
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
