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
        Schema::table('mst_customers', function (Blueprint $table) {
            $table->dropForeign('mst_customers_branch_id_foreign');
            $table->dropForeign('mst_customers_npwp_sub_district_id_foreign');
            $table->dropForeign('mst_customers_npwp_district_id_foreign');
            $table->dropForeign('mst_customers_npwp_city_id_foreign');
            $table->dropForeign('mst_customers_npwp_province_id_foreign');
            $table->dropForeign('mst_customers_sub_district_id_foreign');
            $table->dropForeign('mst_customers_district_id_foreign');
            $table->dropForeign('mst_customers_city_id_foreign');
            $table->dropForeign('mst_customers_province_id_foreign');
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
