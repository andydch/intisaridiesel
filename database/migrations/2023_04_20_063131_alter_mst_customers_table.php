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
            $table->dropIndex('mst_customers_salesman_id2_index');

            $table->unsignedBigInteger('salesman_id')->nullable()->comment('FK ke userdetails')->change();
            $table->unsignedBigInteger('salesman_id2')->nullable()->comment('FK ke userdetails')->change();

            $table->index('salesman_id');
            $table->index('salesman_id2');
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
