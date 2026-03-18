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
            $table->dropForeign('mst_suppliers_province_id_foreign');
            $table->dropForeign('mst_suppliers_city_id_foreign');
            $table->dropForeign('mst_suppliers_district_id_foreign');
            $table->dropForeign('mst_suppliers_sub_district_id_foreign');
            $table->dropForeign('mst_suppliers_country_id_foreign');
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
