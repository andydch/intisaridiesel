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
        Schema::table('tx_purchase_orders', function (Blueprint $table) {
            // $table->dropForeign([
            //     '',
            //     'tx_purchase_orders_company_province_id_foreign',
            //     'tx_purchase_orders_company_city_id_foreign',
            //     'tx_purchase_orders_company_district_id_foreign',
            //     'tx_purchase_orders_company_sub_district_id_foreign'
            // ]);
            // $table->dropColumn([
            //     'company_id',
            //     'company_office_address',
            //     'company_province_id',
            //     'company_city_id',
            //     'company_district_id',
            //     'company_sub_district_id',
            //     'company_npwp_no'
            //     ]);
            $table->dropForeign('tx_purchase_orders_company_id_foreign');
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
