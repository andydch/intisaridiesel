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
        Schema::create('tx_sales_orders', function (Blueprint $table) {
            $table->id();
            $table->string('sales_order_no', 15)->comment('sales order no');
            $table->string('customer_doc_no', 255)->comment('customer document no');
            $table->date('sales_order_date');
            $table->unsignedBigInteger('customer_id')->comment('FK ke master customer');
            $table->unsignedBigInteger('cust_entity_type');
            $table->unsignedBigInteger('cust_supplier_id')->nullable();
            $table->string('cust_office_address', 1024);
            $table->unsignedBigInteger('cust_country_id');
            $table->unsignedBigInteger('cust_province_id')->comment('harus diisi 9999 jika non Indonesia');
            $table->unsignedBigInteger('cust_city_id')->comment('harus diisi 9999 jika non Indonesia');
            $table->unsignedBigInteger('cust_district_id')->comment('harus diisi 9999 jika non Indonesia');
            $table->unsignedBigInteger('cust_sub_district_id')->comment('harus diisi 99999 jika non Indonesia');
            $table->unsignedBigInteger('cust_shipment_address');
            $table->string('post_code', 6);
            $table->string('pic_name', 255)->nullable();
            $table->integer('total_qty');
            $table->decimal('total_before_vat', 20, 2);
            $table->decimal('total_after_vat', 20, 2);
            $table->string('company_info', 2048)->comment('berisi nama, alamat lengkap dan no npwp');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->date('approved_at')->nullable();
            $table->unsignedBigInteger('canceled_by')->nullable();
            $table->date('canceled_at')->nullable();
            $table->char('active', 1)->default('Y');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by');
            $table->timestamps();

            $table->index('sales_order_no');
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
        Schema::dropIfExists('tx_sales_orders');
    }
};
