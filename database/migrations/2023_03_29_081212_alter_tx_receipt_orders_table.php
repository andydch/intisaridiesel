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
            $table->dateTime('draft_at')->nullable()->after('canceled_at');
            $table->dateTime('draft_to_created_at')->nullable()->after('draft_at');
            $table->char('is_draft',1)->nullable()->default('Y')->after('draft_to_created_at');
            $table->unsignedBigInteger('courier_id')->nullable()->after('branch_id');

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
