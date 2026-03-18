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
        Schema::create('tx_purchase_quotations', function (Blueprint $table) {
            $table->id();
            $table->string('quotation_no', 32)->comment('no quotation - unique');
            $table->date('quotation_date');
            $table->unsignedBigInteger('supplier_id')->comment('FK ke table master supplier');
            $table->unsignedBigInteger('supplier_type_id')->nullable()->comment('FK ke table master global');
            $table->unsignedBigInteger('supplier_entity_type_id')->nullable()->comment('FK ke table master global');
            $table->string('supplier_name',255)->nullable();
            $table->string('supplier_office_address',1024)->nullable();
            $table->unsignedBigInteger('supplier_country_id')->nullable()->comment('FK ke table master country');
            $table->unsignedBigInteger('supplier_province_id')->nullable()->comment('FK ke table master province');
            $table->unsignedBigInteger('supplier_city_id')->nullable()->comment('FK ke table master city');
            $table->unsignedBigInteger('supplier_district_id')->nullable()->comment('FK ke table master district');
            $table->unsignedBigInteger('supplier_sub_district_id')->nullable()->comment('FK ke table master sub district');
            $table->string('supplier_post_code',6)->nullable();
            $table->integer('total_qty')->default(0)->nullable();
            $table->unsignedBigInteger('company_id')->nullable()->comment('FK ke table master company');
            $table->unsignedBigInteger('revised_by');
            $table->dateTime('revised_at');

            $table->integer('pic_idx')->nullable()->comment('1: PIC #1; 2: PIC #2');
            $table->char('active', 1)->default('Y');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by');
            $table->timestamps();

            $table->unique('quotation_no');
            $table->foreign('supplier_id')->references('id')->on('mst_suppliers');
            $table->foreign('supplier_type_id')->references('id')->on('mst_globals');
            $table->foreign('supplier_entity_type_id')->references('id')->on('mst_globals');
            $table->foreign('supplier_country_id')->references('id')->on('mst_countries');
            $table->foreign('supplier_province_id')->references('id')->on('mst_provinces');
            $table->foreign('supplier_city_id')->references('id')->on('mst_cities');
            $table->foreign('supplier_sub_district_id')->references('id')->on('mst_sub_districts');
            $table->foreign('company_id')->references('id')->on('mst_companies');
            $table->foreign('revised_by')->references('id')->on('users');
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('updated_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tx_purchase_quotations');
    }
};
