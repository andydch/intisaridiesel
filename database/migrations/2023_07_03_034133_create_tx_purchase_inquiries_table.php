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
        Schema::create('tx_purchase_inquiries', function (Blueprint $table) {
            $table->id();
            $table->string('purchase_inquiry_no',32);
            $table->date('purchase_inquiry_date')->comment('tanggal jurnal');
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
            $table->integer('pic_idx')->nullable()->comment('1: PIC #1; 2: PIC #2');
            $table->text('header')->nullable()->comment('header surat');
            $table->text('footer')->nullable()->comment('footer surat');
            $table->text('remart')->nullable()->comment('remart/catatan terkait purchase inquiry');

            $table->dateTime('draft_at')->nullable();
            $table->dateTime('draft_to_created_at')->nullable();
            $table->char('is_draft',1)->default('N')->nullable();
            $table->char('active', 1)->default('Y');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by');
            $table->unsignedBigInteger('cancel_by');
            $table->timestamps();

            $table->foreign('supplier_id')->references('id')->on('mst_suppliers');
            $table->foreign('supplier_type_id')->references('id')->on('mst_globals');
            $table->foreign('supplier_entity_type_id')->references('id')->on('mst_globals');
            $table->foreign('supplier_country_id')->references('id')->on('mst_countries');
            $table->foreign('supplier_province_id')->references('id')->on('mst_provinces');
            $table->foreign('supplier_city_id')->references('id')->on('mst_cities');
            $table->foreign('supplier_sub_district_id')->references('id')->on('mst_sub_districts');
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('updated_by')->references('id')->on('users');
            $table->foreign('cancel_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tx_purchase_inquiries');
    }
};
