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
        Schema::table('tx_stock_transfers', function (Blueprint $table) {
            $table->dateTime('draft_at')->nullable()->after('remark');
            $table->dateTime('draft_to_created_at')->nullable()->after('draft_at');
            $table->char('is_draft',1)->nullable()->after('draft_to_created_at');
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
