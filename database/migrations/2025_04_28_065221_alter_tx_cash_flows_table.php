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
            $table->string('font_size', 10)->nullable()->after('b_color')->comment('ukuran font');
            $table->string('font_weight', 10)->nullable()->after('font_size')->comment('ketebalan font');
            $table->string('font_style', 10)->nullable()->after('font_weight')->comment('gaya font');
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
