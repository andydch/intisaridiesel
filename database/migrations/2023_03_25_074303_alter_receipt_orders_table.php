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
        Schema::table('tx_receipt_orders', function (Blueprint $table) {
            $table->dropForeign('tx_receipt_orders_company_id_foreign');
            $table->dropColumn('company_id');

            $table->string('invoice_no',255)->nullable()->after('branch_id');
            $table->decimal('invoice_amount',20,2)->nullable()->after('invoice_no');
            $table->decimal('exchange_rate',20,2)->nullable()->after('invoice_amount');
            $table->string('bl_no',255)->nullable()->after('exchange_rate')->comment('BL no');
            $table->string('vessel_no',255)->nullable()->after('bl_no');
            $table->unsignedBigInteger('weight_type_id')->nullable()->comment('FK ke master global')->after('vessel_no');
            $table->decimal('gross_weight',20,2)->nullable()->after('weight_type_id');
            $table->decimal('measurement',20,2)->nullable()->after('gross_weight');
            $table->text('remark')->nullable()->after('measurement');

            $table->foreign('weight_type_id')->references('id')->on('mst_globals');
        });

        Schema::table('tx_receipt_order_parts', function (Blueprint $table) {
            $table->decimal('final_fob',20,2)->nullable()->after('part_price');
            $table->decimal('final_cost',20,2)->nullable()->after('final_fob');
            $table->decimal('total_price',20,2)->nullable()->after('final_cost');
            $table->decimal('total_fob_price',20,2)->nullable()->after('total_price');
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
