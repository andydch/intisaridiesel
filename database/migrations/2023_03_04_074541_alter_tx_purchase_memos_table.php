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
        Schema::table('tx_purchase_memos', function (Blueprint $table) {
            $table->decimal('total_before_vat',20,2)->nullable()->default(0)->after('total_qty');
            $table->decimal('total_after_vat',20,2)->nullable()->default(0)->after('total_before_vat');
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
