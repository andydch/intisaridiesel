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
            $table->dropForeign('mst_parts_weight_id_foreign');
            $table->unsignedBigInteger('weight_id')->default(0)->nullable()->change();
            $table->index('weight_id');
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
