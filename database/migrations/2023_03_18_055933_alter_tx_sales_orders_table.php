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
            $table->char('is_draft')->nullable()->after('company_info');
            $table->dateTime('draft_at')->nullable()->after('is_draft')->comment('tanggal/jam part diterima');
            $table->dateTime('draft_to_created_at')->nullable()->after('draft_at');
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
