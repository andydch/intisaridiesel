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
        Schema::table('mst_parts', function (Blueprint $table) {
            $table->integer('max_stock')->nullable()->default(0)->after('quantity_type_id');
            $table->decimal('final_price', 20, 2)->nullable()->default(0)->after('price_list');

            $table->dropColumn('total_sales_qty');
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
