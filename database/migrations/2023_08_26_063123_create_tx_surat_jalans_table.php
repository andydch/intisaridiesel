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
        Schema::create('tx_surat_jalans', function (Blueprint $table) {
            $table->id();
            $table->string('surat_jalan_no',15)->comment('no surat jalan');
            $table->unsignedBigInteger('sales_quotation_id')->nullable()->comment('FK ke tx_sales_quotation');
            $table->string('customer_doc_no',255)->nullable();
            $table->date('surat_jalan_date');
            $table->dateTime('surat_jalan_expired_date')->nullable()->comment('SJ date + TOP customer');
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('cust_entity_type')->nullable()->comment('FK ke mst_globals');
            $table->string('cust_name',255);
            $table->string('cust_office_address',1024);
            $table->unsignedBigInteger('cust_country_id')->comment('FK ke mst_countries');
            $table->unsignedBigInteger('cust_province_id')->comment('FK ke mst_provinces');
            $table->unsignedBigInteger('cust_city_id')->comment('FK ke mst_cities');
            $table->unsignedBigInteger('cust_district_id')->comment('FK ke mst_districts');
            $table->unsignedBigInteger('cust_sub_district_id')->comment('FK ke mst_sub_districts');
            $table->unsignedBigInteger('cust_shipment_address')->comment('FK ke mst_customer_shipment_address');
            $table->string('post_code',6);
            $table->integer('pic_id')->nullable();
            $table->string('pic_name',255)->nullable();
            $table->string('cust_unit_no',255)->nullable()->comment('cust unit no');
            $table->integer('total_qty');
            $table->decimal('total',20,2);
            $table->char('is_draft',1)->nullable();
            $table->dateTime('draft_at')->nullable()->comment('tgl/jam draft dimulai');
            $table->dateTime('draft_to_created_at')->nullable()->comment('tgl/jam draft jadi created');
            $table->unsignedBigInteger('approved_by')->nullable()->comment('FK ke Users');
            $table->dateTime('approved_at')->nullable();
            $table->unsignedBigInteger('canceled_by')->nullable()->comment('FK ke Users');
            $table->dateTime('canceled_at')->nullable();
            $table->string('reason',2048)->nullable();
            $table->char('need_approval',1)->default('N')->nullable()->comment('dibutuhkan jika harga yang ditawarkan lebih rendah dari avg cost masing2 part');
            $table->text('remark')->nullable();
            $table->unsignedBigInteger('courier_id')->nullable()->comment('FK ke mst_couriers');
            $table->integer('number_of_prints')->default(0)->nullable();
            $table->char('active',1)->default('Y');
            $table->unsignedBigInteger('created_by')->comment('FK ke users');
            $table->unsignedBigInteger('updated_by')->comment('FK ke users');
            $table->timestamps();

            $table->foreign('sales_quotation_id')->references('id')->on('tx_sales_quotations');
            $table->foreign('customer_id')->references('id')->on('mst_customers');
            $table->foreign('cust_entity_type')->references('id')->on('mst_globals');
            $table->foreign('cust_country_id')->references('id')->on('mst_countries');
            $table->foreign('cust_province_id')->references('id')->on('mst_provinces');
            $table->foreign('cust_city_id')->references('id')->on('mst_cities');
            $table->foreign('cust_district_id')->references('id')->on('mst_districts');
            $table->foreign('cust_sub_district_id')->references('id')->on('mst_sub_districts');
            $table->foreign('cust_shipment_address')->references('id')->on('mst_customer_shipment_address');
            $table->foreign('approved_by')->references('id')->on('users');
            $table->foreign('canceled_by')->references('id')->on('users');
            $table->foreign('courier_id')->references('id')->on('mst_couriers');
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
        Schema::dropIfExists('tx_surat_jalans');
    }
};
