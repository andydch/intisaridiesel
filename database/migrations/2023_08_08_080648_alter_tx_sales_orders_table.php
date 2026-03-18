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
        Schema::table('tx_sales_orders', function (Blueprint $table) {
            $table->string('customer_doc_no',255)->nullable()->change();
            $table->unsignedBigInteger('courier_id')->nullable()->comment('FK ke master courier')->after('remark');
            $table->integer('number_of_prints')->default(0)->nullable()->after('courier_id');

            $table->foreign('courier_id')->references('id')->on('mst_couriers');
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
