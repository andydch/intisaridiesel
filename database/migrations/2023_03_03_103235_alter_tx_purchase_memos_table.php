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
            $table->dropForeign('tx_purchase_memos_cancel_by_foreign');
            $table->dropColumn('cancel_by');
            $table->dropColumn('cancel_at');

            $table->date('draft_at')->nullable()->after('canceled_at');
            $table->date('draft_to_created_at')->nullable()->after('draft_at');
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
