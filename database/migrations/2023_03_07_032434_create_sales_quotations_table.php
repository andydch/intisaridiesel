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
        Schema::create('tx_sales_quotations', function (Blueprint $table) {
            $table->id();
            $table->string('sales_quotation_no', 32)->comment('no sales quotation - unique');
            $table->date('sales_quotation_date');
            $table->unsignedBigInteger('customer_id')->comment('FK ke table master customer');
            $table->unsignedBigInteger('customer_type_id')->nullable()->comment('FK ke table master global');
            $table->unsignedBigInteger('customer_entity_type_id')->nullable()->comment('FK ke table master global');
            $table->string('customer_name',255)->nullable();
            $table->string('customer_office_address',1024)->nullable();
            $table->unsignedBigInteger('customer_country_id')->nullable()->comment('FK ke table master country');
            $table->unsignedBigInteger('customer_province_id')->nullable()->comment('FK ke table master province');
            $table->unsignedBigInteger('customer_city_id')->nullable()->comment('FK ke table master city');
            $table->unsignedBigInteger('customer_district_id')->nullable()->comment('FK ke table master district');
            $table->unsignedBigInteger('customer_sub_district_id')->nullable()->comment('FK ke table master sub district');
            $table->string('customer_post_code',6)->nullable();
            $table->integer('total_qty')->default(0)->nullable();
            $table->integer('pic_idx')->nullable()->comment('1: PIC #1; 2: PIC #2');
            $table->char('is_draft',1)->nullable();
            $table->dateTime('draft_at')->nullable();
            $table->dateTime('draft_to_created_at')->nullable();
            $table->unsignedBigInteger('cancel_by')->nullable();
            $table->dateTime('cancel_time')->nullable();
            $table->char('active', 1)->default('Y');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by');
            $table->timestamps();

            $table->unique('sales_quotation_no');
            $table->foreign('customer_id')->references('id')->on('mst_customers');
            $table->foreign('customer_type_id')->references('id')->on('mst_globals');
            $table->foreign('customer_entity_type_id')->references('id')->on('mst_globals');
            $table->foreign('customer_country_id')->references('id')->on('mst_countries');
            $table->foreign('customer_province_id')->references('id')->on('mst_provinces');
            $table->foreign('customer_city_id')->references('id')->on('mst_cities');
            $table->foreign('customer_sub_district_id')->references('id')->on('mst_sub_districts');
            $table->foreign('cancel_by')->references('id')->on('users');
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
        Schema::dropIfExists('sales_sales_quotations');
    }
};
