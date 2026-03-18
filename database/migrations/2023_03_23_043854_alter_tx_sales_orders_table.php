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
        Schema::table('tx_sales_orders', function (Blueprint $table) {
            $table->dropForeign('tx_sales_orders_company_id_foreign');
            $table->dropColumn('company_id');
            $table->dropColumn('company_info');

            $table->text('remark')->nullable()->after('need_approval');
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
