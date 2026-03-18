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
            $table->unsignedBigInteger('branch_id')->nullable()->after('cust_email')->comment('cabang terkait dengan customer');
            $table->string('customer_unique_code',5)->nullable()->after('entity_type_id')->comment('kode unik untuk setiap customer');

            $table->foreign('branch_id')->references('id')->on('mst_branches');
            $table->unique('customer_unique_code');
        });

        Schema::table('mst_customer_shipment_address', function (Blueprint $table) {
            $table->string('post_code',6)->nullable()->after('phone');
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
