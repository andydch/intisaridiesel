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
        Schema::table('tx_stock_transfers', function (Blueprint $table) {
            $table->date('stock_transfer_date')->nullable()->change();
        });

        Schema::table('tx_stock_transfer_parts', function (Blueprint $table) {
            $table->decimal('last_avg_cost', 20, 2)->nullable()->comment('status avg terakhir')->after('qty');
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
