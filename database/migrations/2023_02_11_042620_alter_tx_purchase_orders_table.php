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
            $table->unsignedBigInteger('supplier_type_id')->nullable()->after('supplier_id');
            $table->unsignedBigInteger('supplier_entity_type_id')->nullable()->after('supplier_type_id');
            $table->string('supplier_name', 255)->nullable()->after('supplier_entity_type_id');
            $table->string('supplier_office_address', 1024)->nullable()->after('supplier_name');
            $table->unsignedBigInteger('supplier_country_id')->nullable()->after('supplier_office_address');
            $table->unsignedBigInteger('supplier_province_id')->nullable()->after('supplier_country_id');
            $table->unsignedBigInteger('supplier_city_id')->nullable()->after('supplier_province_id');
            $table->unsignedBigInteger('supplier_district_id')->nullable()->after('supplier_city_id');
            $table->unsignedBigInteger('supplier_sub_district_id')->nullable()->after('supplier_district_id');
            $table->string('supplier_post_code', 6)->nullable()->after('supplier_sub_district_id');
            $table->integer('total_qty')->default(0)->nullable()->after('pic_idx')->comment('total qty dari part detail');
            $table->decimal('total_before_vat', 20, 2)->default(0)->nullable()->after('total_qty')->comment('total harga sebelum PPN');
            $table->decimal('total_after_vat', 20, 2)->default(0)->nullable()->after('total_before_vat')->comment('total harga sesudah PPN');
            $table->string('branch_address', 1024)->nullable()->after('branch_id');

            $table->string('company_office_address', 1024)->nullable()->after('company_id');
            $table->unsignedBigInteger('company_province_id')->nullable()->after('company_office_address');
            $table->unsignedBigInteger('company_city_id')->nullable()->after('company_province_id');
            $table->unsignedBigInteger('company_district_id')->nullable()->after('company_city_id');
            $table->unsignedBigInteger('company_sub_district_id')->nullable()->after('company_district_id');
            $table->string('company_npwp_no', 12)->nullable()->after('company_district_id');

            $table->unsignedBigInteger('cancel_by')->nullable()->after('branch_address');
            $table->dateTime('cancel_at')->nullable()->after('cancel_by');
            $table->unsignedBigInteger('approve_by')->nullable()->after('cancel_at');
            $table->dateTime('approve_at')->nullable()->after('approve_by');

            $table->foreign('supplier_type_id')->references('id')->on('mst_globals');
            $table->foreign('supplier_entity_type_id')->references('id')->on('mst_globals');
            $table->foreign('supplier_country_id')->references('id')->on('mst_countries');
            $table->foreign('supplier_province_id')->references('id')->on('mst_provinces');
            $table->foreign('supplier_city_id')->references('id')->on('mst_cities');
            $table->foreign('supplier_district_id')->references('id')->on('mst_districts');
            $table->foreign('supplier_sub_district_id')->references('id')->on('mst_sub_districts');
            $table->foreign('company_province_id')->references('id')->on('mst_provinces');
            $table->foreign('company_city_id')->references('id')->on('mst_cities');
            $table->foreign('company_district_id')->references('id')->on('mst_districts');
            $table->foreign('company_sub_district_id')->references('id')->on('mst_sub_districts');
            $table->foreign('cancel_by')->references('id')->on('users');
            $table->foreign('approve_by')->references('id')->on('users');

            $table->string('purchase_no', 12)->change();
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
