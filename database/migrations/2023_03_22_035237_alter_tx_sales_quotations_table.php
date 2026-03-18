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
        Schema::table('tx_sales_quotations', function (Blueprint $table) {
            $table->text('header')->nullable()->after('total_qty');
            $table->text('footer')->nullable()->after('header');
            $table->text('remark')->nullable()->after('footer');
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
