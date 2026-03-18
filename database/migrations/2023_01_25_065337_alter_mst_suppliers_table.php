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
        Schema::table('mst_suppliers', function (Blueprint $table) {
            // $table->dropForeign(['npwp_province_id', 'npwp_city_id', 'npwp_district_id', 'npwp_sub_district_id']);
            $table->dropForeign('mst_suppliers_npwp_province_id_foreign');
            $table->dropForeign('mst_suppliers_npwp_city_id_foreign');
            $table->dropForeign('mst_suppliers_npwp_district_id_foreign');
            $table->dropForeign('mst_suppliers_npwp_sub_district_id_foreign');
            $table->dropForeign('mst_suppliers_currency2_foreign');
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
